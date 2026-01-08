<?php
/**
 * REST API endpoints class
 */
class Family_Media_Manager_API {

    /**
     * Plugin name
     */
    private $plugin_name;

    /**
     * Plugin version
     */
    private $version;

    /**
     * Initialize the class
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        $namespace = 'family-gallery/v1';

        // Upload photo
        register_rest_route($namespace, '/upload', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'upload_media'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Get gallery
        register_rest_route($namespace, '/gallery', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_gallery'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Get single media
        register_rest_route($namespace, '/media/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_media'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Get download URL
        register_rest_route($namespace, '/media/(?P<id>\d+)/download', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_download_url'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Delete media
        register_rest_route($namespace, '/media/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array($this, 'delete_media'),
            'permission_callback' => array($this, 'check_auth'),
        ));

        // Authentication
        register_rest_route($namespace, '/auth/login', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'login'),
            'permission_callback' => '__return_true',
        ));
        
        // Albums
        register_rest_route($namespace, '/albums', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_albums'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        register_rest_route($namespace, '/albums', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_album'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        register_rest_route($namespace, '/albums/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_album'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        register_rest_route($namespace, '/albums/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'update_album'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        register_rest_route($namespace, '/albums/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array($this, 'delete_album'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        // Comments
        register_rest_route($namespace, '/media/(?P<id>\d+)/comments', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_comments'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        register_rest_route($namespace, '/media/(?P<id>\d+)/comments', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'add_comment'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        register_rest_route($namespace, '/comments/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array($this, 'delete_comment'),
            'permission_callback' => array($this, 'check_auth'),
        ));
    }

    /**
     * Check authentication
     */
    public function check_auth() {
        return is_user_logged_in();
    }

    /**
     * Upload media endpoint
     */
    public function upload_media($request) {
        $files = $request->get_file_params();
        $params = $request->get_params();

        if (empty($files['photo'])) {
            return new WP_Error('no_file', 'No file uploaded', array('status' => 400));
        }

        $caption = isset($params['caption']) ? $params['caption'] : '';
        $album_id = isset($params['album_id']) ? $params['album_id'] : null;

        $result = Family_Media_Manager_Uploader::handle_upload($files['photo'], $caption, $album_id);

        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_Error('upload_failed', $result['error'], array('status' => 500));
        }
    }

    /**
     * Get gallery endpoint
     */
    public function get_gallery($request) {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $user_id = $request->get_param('user_id');

        $result = Family_Media_Manager_Media_Library::get_media(array(
            'page'     => $page,
            'per_page' => $per_page,
            'user_id'  => $user_id
        ));

        // Format response
        $photos = array();
        foreach ($result['media'] as $media) {
            $photos[] = array(
                'id'            => $media->id,
                'thumbnail_url' => $this->get_thumbnail_url($media->thumbnail_path),
                'filename'      => $media->filename,
                'file_type'     => $media->file_type,
                'upload_date'   => $media->upload_date,
                'caption'       => $media->caption,
                'owner_id'      => $media->owner_id
            );
        }

        return new WP_REST_Response(array(
            'photos' => $photos,
            'total'  => $result['total'],
            'pages'  => $result['pages']
        ), 200);
    }

    /**
     * Get single media endpoint
     */
    public function get_media($request) {
        $media_id = $request->get_param('id');
        $media = Family_Media_Manager_Media_Library::get_media_by_id($media_id);

        if (!$media) {
            return new WP_Error('not_found', 'Media not found', array('status' => 404));
        }

        // Check access
        if (!Family_Media_Manager_Sharing::user_can_access($media_id, get_current_user_id())) {
            return new WP_Error('forbidden', 'Access denied', array('status' => 403));
        }

        return new WP_REST_Response(array(
            'id'            => $media->id,
            'thumbnail_url' => $this->get_thumbnail_url($media->thumbnail_path),
            'filename'      => $media->filename,
            'file_type'     => $media->file_type,
            'file_size'     => $media->file_size,
            'upload_date'   => $media->upload_date,
            'taken_date'    => $media->taken_date,
            'caption'       => $media->caption,
            'owner_id'      => $media->owner_id
        ), 200);
    }

    /**
     * Get download URL endpoint
     */
    public function get_download_url($request) {
        $media_id = $request->get_param('id');
        $media = Family_Media_Manager_Media_Library::get_media_by_id($media_id);

        if (!$media) {
            return new WP_Error('not_found', 'Media not found', array('status' => 404));
        }

        // Check access
        if (!Family_Media_Manager_Sharing::user_can_access($media_id, get_current_user_id())) {
            return new WP_Error('forbidden', 'Access denied', array('status' => 403));
        }

        $cloud = new Family_Media_Manager_Cloud_Storage($media->owner_id, $media->cloud_provider);
        $download_url = $cloud->get_download_url($media->cloud_file_id);

        if (!$download_url) {
            return new WP_Error('error', 'Could not generate download URL', array('status' => 500));
        }

        return new WP_REST_Response(array(
            'download_url' => $download_url,
            'expires_in'   => 3600
        ), 200);
    }

    /**
     * Delete media endpoint
     */
    public function delete_media($request) {
        $media_id = $request->get_param('id');
        $media = Family_Media_Manager_Media_Library::get_media_by_id($media_id);

        if (!$media) {
            return new WP_Error('not_found', 'Media not found', array('status' => 404));
        }

        // Only owner can delete
        if ($media->owner_id != get_current_user_id()) {
            return new WP_Error('forbidden', 'Only the owner can delete media', array('status' => 403));
        }

        // Delete thumbnail
        if ($media->thumbnail_path) {
            Family_Media_Manager_Thumbnail::delete($media->thumbnail_path);
        }

        // Delete from database
        Family_Media_Manager_Media_Library::delete_media($media_id);

        return new WP_REST_Response(array('success' => true), 200);
    }

    /**
     * Login endpoint
     */
    public function login($request) {
        $email = $request->get_param('email');
        $code = $request->get_param('code');

        if (!$email || !$code) {
            return new WP_Error('missing_params', 'Email and code required', array('status' => 400));
        }

        // TODO: Implement magic link / code authentication
        // For now, use standard WordPress authentication
        
        return new WP_REST_Response(array(
            'message' => 'Login endpoint - to be implemented'
        ), 501);
    }

    /**
     * Get thumbnail URL helper
     */
    private function get_thumbnail_url($thumbnail_path) {
        if (!$thumbnail_path) {
            return '';
        }

        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/family-gallery/' . basename($thumbnail_path);
    }
    
    /**
     * Get albums endpoint
     */
    public function get_albums($request) {
        $albums = Family_Media_Manager_Albums::get_albums();
        
        $formatted = array();
        foreach ($albums as $album) {
            $formatted[] = array(
                'id'              => $album->id,
                'name'            => $album->name,
                'description'     => $album->description,
                'photo_count'     => (int) $album->photo_count,
                'created_date'    => $album->created_date,
                'last_photo_date' => $album->last_photo_date,
                'owner_id'        => $album->owner_id
            );
        }
        
        return new WP_REST_Response($formatted, 200);
    }
    
    /**
     * Create album endpoint
     */
    public function create_album($request) {
        $name = $request->get_param('name');
        $description = $request->get_param('description') ?: '';
        
        if (!$name) {
            return new WP_Error('missing_name', 'Album name required', array('status' => 400));
        }
        
        $album_id = Family_Media_Manager_Albums::create_album($name, $description);
        
        if ($album_id) {
            return new WP_REST_Response(array(
                'success'  => true,
                'album_id' => $album_id
            ), 201);
        }
        
        return new WP_Error('error', 'Failed to create album', array('status' => 500));
    }
    
    /**
     * Get album endpoint
     */
    public function get_album($request) {
        $album_id = $request->get_param('id');
        $album = Family_Media_Manager_Albums::get_album($album_id);
        
        if (!$album) {
            return new WP_Error('not_found', 'Album not found', array('status' => 404));
        }
        
        // Check access
        if (!Family_Media_Manager_Albums::user_can_access($album_id, get_current_user_id())) {
            return new WP_Error('forbidden', 'Access denied', array('status' => 403));
        }
        
        // Get photos in album
        $media_result = Family_Media_Manager_Albums::get_album_media($album_id);
        
        $photos = array();
        foreach ($media_result['media'] as $media) {
            $photos[] = array(
                'id'            => $media->id,
                'thumbnail_url' => $this->get_thumbnail_url($media->thumbnail_path),
                'filename'      => $media->filename,
                'file_type'     => $media->file_type,
                'upload_date'   => $media->upload_date,
                'caption'       => $media->caption
            );
        }
        
        return new WP_REST_Response(array(
            'id'          => $album->id,
            'name'        => $album->name,
            'description' => $album->description,
            'photos'      => $photos,
            'photo_count' => count($photos),
            'created_date'=> $album->created_date,
            'owner_id'    => $album->owner_id
        ), 200);
    }
    
    /**
     * Update album endpoint
     */
    public function update_album($request) {
        $album_id = $request->get_param('id');
        $album = Family_Media_Manager_Albums::get_album($album_id);
        
        if (!$album) {
            return new WP_Error('not_found', 'Album not found', array('status' => 404));
        }
        
        // Only owner can update
        if ($album->owner_id != get_current_user_id()) {
            return new WP_Error('forbidden', 'Only owner can update album', array('status' => 403));
        }
        
        $data = array();
        if ($request->get_param('name')) {
            $data['name'] = $request->get_param('name');
        }
        if ($request->get_param('description')) {
            $data['description'] = $request->get_param('description');
        }
        
        $result = Family_Media_Manager_Albums::update_album($album_id, $data);
        
        return new WP_REST_Response(array('success' => $result !== false), 200);
    }
    
    /**
     * Delete album endpoint
     */
    public function delete_album($request) {
        $album_id = $request->get_param('id');
        $album = Family_Media_Manager_Albums::get_album($album_id);
        
        if (!$album) {
            return new WP_Error('not_found', 'Album not found', array('status' => 404));
        }
        
        // Only owner can delete
        if ($album->owner_id != get_current_user_id()) {
            return new WP_Error('forbidden', 'Only owner can delete album', array('status' => 403));
        }
        
        Family_Media_Manager_Albums::delete_album($album_id);
        
        return new WP_REST_Response(array('success' => true), 200);
    }
    
    /**
     * Get comments endpoint
     */
    public function get_comments($request) {
        $media_id = $request->get_param('id');
        
        // Check access to media
        if (!Family_Media_Manager_Sharing::user_can_access($media_id, get_current_user_id())) {
            return new WP_Error('forbidden', 'Access denied', array('status' => 403));
        }
        
        $comments = Family_Media_Manager_Comments::get_comments($media_id);
        
        $formatted = array();
        foreach ($comments as $comment) {
            $formatted[] = array(
                'id'           => $comment->id,
                'user_id'      => $comment->user_id,
                'user_name'    => $comment->user_name,
                'comment_text' => $comment->comment_text,
                'created_date' => $comment->created_date
            );
        }
        
        return new WP_REST_Response($formatted, 200);
    }
    
    /**
     * Add comment endpoint
     */
    public function add_comment($request) {
        $media_id = $request->get_param('id');
        $comment_text = $request->get_param('comment');
        
        if (!$comment_text) {
            return new WP_Error('missing_comment', 'Comment text required', array('status' => 400));
        }
        
        $comment_id = Family_Media_Manager_Comments::add_comment($media_id, $comment_text);
        
        if ($comment_id) {
            return new WP_REST_Response(array(
                'success'    => true,
                'comment_id' => $comment_id
            ), 201);
        }
        
        return new WP_Error('error', 'Failed to add comment', array('status' => 500));
    }
    
    /**
     * Delete comment endpoint
     */
    public function delete_comment($request) {
        $comment_id = $request->get_param('id');
        
        $result = Family_Media_Manager_Comments::delete_comment($comment_id);
        
        if ($result) {
            return new WP_REST_Response(array('success' => true), 200);
        }
        
        return new WP_Error('error', 'Failed to delete comment', array('status' => 500));
    }
}
