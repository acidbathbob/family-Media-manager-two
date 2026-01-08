<?php
/**
 * Admin family members page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get all users
$users = get_users();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <p>Manage family members who have access to the gallery. You can add new users or manage existing ones.</p>

    <h2>Current Family Members</h2>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Cloud Storage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <?php
                $cloud = new Family_Media_Manager_Cloud_Storage($user->ID);
                $is_connected = $cloud->is_connected();
                ?>
                <tr>
                    <td><?php echo esc_html($user->display_name); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                    <td>
                        <?php if ($is_connected): ?>
                            <span style="color: green;">✓ Connected</span>
                        <?php else: ?>
                            <span style="color: gray;">Not connected</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo get_edit_user_link($user->ID); ?>" class="button button-small">
                            Edit
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <p>
        <a href="<?php echo admin_url('user-new.php'); ?>" class="button button-primary">
            Add New Family Member
        </a>
    </p>
</div>
