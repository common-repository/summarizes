<?php

/*
Plugin Name: Summarizes
Description: Uses the latest cutting-edge AI to summarize your blog, add pull quotes, and create FAQs.
Version: 2.0.0
Author: Big Data Institute International Ltd
License:     GPLv2
*/

if ( !function_exists( 'summarizes_fs' ) ) {
    // Create a helper function for easy SDK access.
    function summarizes_fs()
    {
        global  $summarizes_fs ;
        
        if ( !isset( $summarizes_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $summarizes_fs = fs_dynamic_init( array(
                'id'             => '12317',
                'slug'           => 'summarizes',
                'type'           => 'plugin',
                'public_key'     => 'pk_bdef4761f71822bf5ce25441e782a',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'navigation'     => 'tabs',
                'menu'           => array(
                'slug'    => 'summarizes',
                'account' => false,
                'support' => false,
                'parent'  => array(
                'slug' => 'options-general.php',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $summarizes_fs;
    }
    
    // Init Freemius.
    summarizes_fs();
    // Signal that SDK was initiated.
    do_action( 'summarizes_fs_loaded' );
}

if ( !function_exists( 'summarizes_format_summary' ) ) {
    // Function to format summary added to beginning of post
    function summarizes_format_summary( string $summary_title, array $post_summary_array ) : string
    {
        $html_id = get_option( 'summarizes_html_id', 'summarizes_summary_id' );
        $html_css = get_option( 'summarizes_html_css', 'summarizes_summary_css' );
        // Create the div to hold the list
        $post_summary = '<div class="' . $html_css . '" id="' . $html_id . '">' . $summary_title;
        // Check if the disclosure should be added
        if ( get_option( 'summarizes_disclose_ai', false ) ) {
            $post_summary .= '<span> (AI Summaries by <a href="https://www.summariz.es" target="_blank" style="">Summarizes</a>)</span>';
        }
        $post_summary .= "<br><ul>";
        // Format out the summary items
        foreach ( $post_summary_array as $post_summary_item ) {
            $post_summary .= "<li>" . $post_summary_item . "</li>";
        }
        $post_summary .= "</ul></div>";
        return $post_summary;
    }

}

if ( !function_exists( 'summarizes_add_summary_to_post' ) ) {
    // Function to add summary to the post
    function summarizes_add_summary_to_post( string $content ) : string
    {
        
        if ( is_single() ) {
            $summary_title = get_option( 'summarizes_summary_title', 'Blog Summary:' );
            $post_summary_array = get_post_meta( get_post()->ID, "summarizes_summary", true );
            // Only add summaries for blog posts that have summaries
            
            if ( $post_summary_array != '' ) {
                
                if ( summarizes_fs()->can_use_premium_code() ) {
                    $content = summarizes_add_pull_quote_to_post__premium_only( $content );
                    $content = summarizes_add_faq_to_post__premium_only( $content );
                }
                
                // Reduce number of summaries to what user wants or fewer
                $number_of_summaries = get_option( 'summarizes_number_of_summaries', 1 );
                $post_summary_array = array_slice( $post_summary_array, 0, $number_of_summaries );
                summarizes_log( "summarizes_add_summary_to_post post_summary_array\"" . summarizes_implode_recursive( $post_summary_array ) . "\"", false );
                $content = summarizes_format_summary( $summary_title, $post_summary_array ) . $content;
            }
        
        }
        
        return $content;
    }
    
    add_filter( 'the_content', 'summarizes_add_summary_to_post' );
}

if ( !function_exists( 'summarizes_add_pull_quote_to_post__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_add_faq_to_post__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_find_pull_quote_location__premium_only' ) ) {
}

if ( !function_exists( 'summarizes_options_page' ) ) {
    // Register the options page in the WordPress admin.
    function summarizes_options_page()
    {
        add_options_page(
            'Summarizes Options',
            'Summarizes',
            'manage_options',
            'summarizes',
            'summarizes_options_page_content'
        );
    }
    
    add_action( 'admin_menu', 'summarizes_options_page' );
}

if ( !function_exists( 'summarizes_handle_free_settings' ) ) {
    function summarizes_handle_free_settings()
    {
        
        if ( isset( $_POST['summary_title'] ) && isset( $_POST['html_id'] ) && isset( $_POST['html_css'] ) ) {
            $sanitized_summary_title = wp_kses_post( $_POST['summary_title'], wp_kses_allowed_html( 'strip' ) );
            update_option( 'summarizes_summary_title', $sanitized_summary_title );
            $sanitized_html_id = sanitize_html_class( $_POST['html_id'] );
            
            if ( $sanitized_html_id != '' ) {
                update_option( 'summarizes_html_id', $sanitized_html_id );
            } else {
                echo  '<div class="notice notice-error">The HTML class is invalid. The value was ' . wp_kses( $_POST['html_id'], wp_kses_allowed_html( 'strip' ) ) . '</div>' ;
            }
            
            $sanitized_html_class = sanitize_html_class( $_POST['html_css'] );
            
            if ( $sanitized_html_class != '' ) {
                update_option( 'summarizes_html_css', $sanitized_html_class );
            } else {
                echo  '<div class="notice notice-error">The HTML class is invalid. The value was ' . wp_kses( $_POST['html_css'], wp_kses_allowed_html( 'strip' ) ) . '</div>' ;
            }
            
            
            if ( isset( $_POST['debug_mode'] ) ) {
                $summarizes_debug_mode = true;
            } else {
                $summarizes_debug_mode = false;
            }
            
            update_option( 'summarizes_debug_mode', $summarizes_debug_mode );
            
            if ( isset( $_POST['disclose_ai'] ) ) {
                $summarizes_disclose_ai = true;
            } else {
                $summarizes_disclose_ai = false;
            }
            
            update_option( 'summarizes_disclose_ai', $summarizes_disclose_ai );
            echo  '<div class="notice notice-success"><p>Options updated.</p></div>' ;
        }
    
    }

}
if ( !function_exists( 'summarizes_handle_premium_settings__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_handle_premium_pull_quote_settings__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_handle_premium_faq_settings__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_options_page_content' ) ) {
    // Define the content of the options page.
    function summarizes_options_page_content()
    {
        // If the form has been submitted, save the options.
        summarizes_handle_free_settings();
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            summarizes_handle_premium_settings__premium_only();
            summarizes_handle_premium_pull_quote_settings__premium_only();
            summarizes_handle_premium_faq_settings__premium_only();
        }
        
        // Retrieve the current options.
        $html_id = get_option( 'summarizes_html_id', 'summarizes_summary_id' );
        $html_css = get_option( 'summarizes_html_css', 'summarizes_summary_css' );
        $disclose_ai = get_option( 'summarizes_disclose_ai', false );
        $debug_mode = get_option( 'summarizes_debug_mode', false );
        $batch_size = get_option( 'summarizes_batch_size', 3 );
        $number_of_summaries = get_option( 'summarizes_number_of_summaries', 1 );
        // TODO: summary types aren't being used
        $summary_type = summarizes_get_summary_type();
        $summary_title = get_option( 'summarizes_summary_title', 'Blog Summary:' );
        $tier = summarizes_get_tier();
        $pull_quote_enable = get_option( 'summarizes_pull_quote_enable', true );
        $pull_quote_number_per_page = get_option( 'summarizes_pull_quote_number_per_page', 2 );
        $pull_quote_begin_html = get_option( 'summarizes_pull_quote_begin_html', '<blockquote class="right quote">' );
        $pull_quote_end_html = get_option( 'summarizes_pull_quote_end_html', '</blockquote>' );
        $pull_quote_skip_existing = get_option( 'summarizes_pull_quote_skip_existing', true );
        $faq_enable = get_option( 'summarizes_faq_enable', true );
        $faq_title = get_option( 'summarizes_faq_title', 'Frequently Asked Questions' );
        $faq_html_id = get_option( 'summarizes_faq_html_id', 'summarizes_faq_id' );
        $faq_html_css = get_option( 'summarizes_faq_html_css', 'summarizes_faq_css' );
        summarizes_log( "Current settings batch_size=\"{$batch_size}\" number_of_summaries=\"{$number_of_summaries}\" tier=\"" . $tier . "\" license=\"" . summarizes_get_license() . "\" summary_type=\"{$summary_type}\" summary_title=\"{$summary_title}\" html_id=\"{$html_id}\" html_css=\"{$html_css}\" debug_mode=\"{$debug_mode}\" disclose_ai=\"{$disclose_ai}\"", false );
        $tab = ( isset( $_GET['tab'] ) ? sanitize_html_class( $_GET['tab'] ) : "freesettings" );
        // TODO: Add debug output tab
        // Display the options form.
        summarizes_show_tab_options( $tab );
        if ( $tab == 'mainpage' ) {
            
            if ( summarizes_fs()->is_free_plan() ) {
                summarizes_show_upgrade_reasons();
                summarizes_show_summary_examples();
            } else {
                ?>
                <h1>Welcome to Summarizes Pro</h1>
            <?php 
                summarizes_show_summary_examples();
            }
        
        }
        summarizes_show_free_settings(
            $tab,
            $summary_title,
            $html_id,
            $html_css,
            $debug_mode,
            $disclose_ai
        );
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            summarizes_show_pro_settings__premium_only(
                $tab,
                $batch_size,
                $number_of_summaries,
                $tier,
                $summary_type
            );
            summarizes_show_pro_pull_quote_settings__premium_only(
                $tab,
                $pull_quote_enable,
                $pull_quote_number_per_page,
                $pull_quote_begin_html,
                $pull_quote_end_html,
                $pull_quote_skip_existing
            );
            summarizes_show_pro_faq_settings__premium_only(
                $tab,
                $faq_enable,
                $faq_title,
                $faq_html_id,
                $faq_html_css
            );
        }
    
    }

}
if ( !function_exists( 'summarizes_show_tab_options' ) ) {
    function summarizes_show_tab_options( $tab )
    {
        ?>
            <div class="wrap fs-section fs-full-size-wrapper" style="margin-left: 1em;">
                <h2 class="nav-tab-wrapper" id="settings">
                    <a href="?page=summarizes&tab=mainpage" class="nav-tab fs-tab home <?php 
        if ( $tab === 'mainpage' ) {
            ?>nav-tab-active<?php 
        }
        ?>">Main</a>
                    <a href="?page=summarizes&tab=freesettings" class="nav-tab fs-tab home <?php 
        if ( $tab === 'freesettings' ) {
            ?>nav-tab-active<?php 
        }
        ?>">Settings</a>
            <?php 
        if ( summarizes_fs()->can_use_premium_code() ) {
            summarizes_add_pro_settings_tab__premium_only( $tab );
        }
        ?>
                </h2>

            <?php 
    }

}
if ( !function_exists( 'summarizes_show_upgrade_reasons' ) ) {
    function summarizes_show_upgrade_reasons()
    {
        ?>
                    <h1>Video Demonstrating Summarizes Pro Features</h1>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/i-s4D-D40SE?start=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

                    <h1>Unlock Powerful Pro Features</h1>
                    <ul style="list-style: '✓  ' outside none;">
                        <li><strong>Configurable number of sentences of summarization</strong> - You choose the maximum number of sentences for your summary.</li>
                        <li><strong>Bulk Summarization</strong> - Bulk summarization to summarize all blog posts for your site.</li>
                        <li><strong>FAQ creation</strong> - Automatically generate FAQs from your blog content.</li>
                        <li><strong>Pull Quotes</strong> - Identify and extract quotes from the blog to highlight as pull quotes.</li>
                        <li><strong>Summarization Styles</strong> - Choose from different styles of summarization to tailor the output to your needs.</li>
                        <li><strong>Bulk Re-Summarize for Better AI Models</strong> - As newer and better AI models become available, you can easily bulk re-summarize your content to leverage their capabilities.</li>
                        <li><strong>Summarize Multiple Sites</strong> - Purchase the multi-site license to summarize content across several of your websites.</li>
                    </ul>
                    <form action="https://checkout.freemius.com/mode/dialog/plugin/12317/plan/20924/">
                        <input class="button button-primary" type="submit" value="Upgrade to Summarizes Pro" />
                    </form>

                    <?php 
    }

}
if ( !function_exists( 'summarizes_add_pro_settings_tab__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_show_summary_examples' ) ) {
    function summarizes_show_summary_examples()
    {
        ?>
                    <p>Examples of the same post with different summary styles.</p>

                <style>
                    tr:nth-child(odd) {
                        background-color: #D7D7D7;
                    }
                </style>
                <table style="max-width: 600px;">
                    <thead>
                        <tr>
                            <th style="max-width:100px;">Summary Style</th>
                            <th>Summary Output</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row"><label for="summary_title">Executive (default)</label></th>
                            <td>Confluent, a leading data streaming platform, recently announced its acquisition of Immerok, a startup focused on providing low-latency data processing solutions for real-time analytics</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Marketing</label></th>
                            <td>Confluent's acquisition of Immerok is expected to enhance their data processing and analytics capabilities, particularly in real-time data processing for sectors such as finance, healthcare, and e-commerce</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Hypey</label></th>
                            <td>Confluent, the industry-leading data streaming platform, has made a huge move by acquiring Immerok, the revolutionary startup specializing in low-latency data processing for real-time analytics</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Cringey</label></th>
                            <td>Confluent, the data streaming platform that everyone's talking about, has just gone and acquired Immerok, the low-latency data processing startup that's causing a stir in the industry</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Dry Humor</label></th>
                            <td>Confluent, the data streaming platform that has long been the darling of the industry, has made a move that is sure to raise some eyebrows by acquiring Immerok, the up-and-coming startup specializing in low-latency data processing for real-time analytics</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Tech Bro</label></th>
                            <td>Confluent, the data streaming platform that's already a big shot in the industry, has just made a power move by acquiring Immerok, a startup that specializes in low-latency data processing for real-time analytics</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Technical</label></th>
                            <td>Confluent, a leading data streaming platform, has recently acquired Immerok, a startup specializing in low-latency data processing for real-time analytics</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Funny</label></th>
                            <td>Confluent, the data streaming platform that's already been crushing the game, has just made a big move by acquiring Immerok, a startup that's been doing some pretty cool stuff with real-time data processing</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Hippie</label></th>
                            <td>Hey man, so Confluent, the data streaming platform, just took a big step towards peace and love by acquiring Immerok, a startup that's been doing some awesome stuff with real-time data processing</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Dracula</label></th>
                            <td>Confluent, the data streaming platform, has just acquired Immerok, a startup that's been dabbling in the dark arts of real-time data processing</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Caveman</label></th>
                            <td>Confluent, big data thing, buy Immerok, small data thing</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Arnold (Action Hero)</label></th>
                            <td>Confluent, the data streaming platform, just went and acquired Immerok, a startup that's been doing some pretty impressive stuff with real-time data processing</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">SEO</label></th>
                            <td>Confluent, the data streaming platform, has acquired Immerok, a startup specializing in real-time data processing</td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_title">Gangsta</label></th>
                            <td>So, Confluent just copped Immerok, a start-up that's been killing it in the real-time data game</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>We are not affiliated with Confluent in any way. This is an example taken from a long-form post about Confluent.</strong></p>

            <?php 
    }

}
if ( !function_exists( 'summarizes_show_free_settings' ) ) {
    function summarizes_show_free_settings(
        $tab,
        $summary_title,
        $html_id,
        $html_css,
        $debug_mode,
        $disclose_ai
    )
    {
        
        if ( $tab == 'freesettings' ) {
            ?>
            <form method="post">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="summary_title">Summary Title</label><br />(The text displayed on the post right before the summary)</th>
                            <td><textarea name="summary_title" id="summary_title" class="regular-text"><?php 
            echo  esc_textarea( $summary_title ) ;
            ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="html_id">HTML ID</label><br />(The HTML ID for doing styling)</th>
                            <td><textarea name="html_id" id="html_id" class="regular-text"><?php 
            echo  esc_textarea( $html_id ) ;
            ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="html_css">HTML CSS</label><br />(The name of the CSS class to apply to the summary)</th>
                            <td><textarea name="html_css" id="html_css" class="regular-text"><?php 
            echo  esc_textarea( $html_css ) ;
            ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="disclose_ai">Disclose AI Usage</label><br/>(Adds a note that summaries are created with AI. May be required in some jurisdictions.)</th>
                            <td><input type="checkbox" name="disclose_ai" id="disclose_ai" class="regular-text" <?php 
            if ( $disclose_ai == true ) {
                echo  "checked" ;
            }
            ?> /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="debug_mode">Debug Mode</label><br/>(Extra logging for troubleshooting)</th>
                            <td><input type="checkbox" name="debug_mode" id="debug_mode" class="regular-text" <?php 
            if ( $debug_mode == true ) {
                echo  "checked" ;
            }
            ?> /></td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
            </form>

        <?php 
        }
    
    }

}
if ( !function_exists( 'summarizes_show_pro_settings__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_show_pro_pull_quote_settings__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_show_pro_faq_settings__premium_only' ) ) {
}

if ( !function_exists( 'summarize_single_post' ) ) {
    // Called when a post is published or updated to (re-)summarize it
    function summarize_single_post( int $post_ID, WP_Post $post ) : bool
    {
        summarizes_log( "resummarize_updated_post starting to summarize post_ID=\"" . $post_ID . "\"", false );
        $posts_to_summarize = [];
        $the_post = get_post( $post_ID );
        // Get the array ready to put into JSON
        $new_blog_post = apply_filters( 'the_content', $post->post_content );
        
        if ( strlen( $new_blog_post ) >= 20 ) {
            $post_array = summarizes_prepare_post_array( $the_post, $new_blog_post );
            // Add the new post to the list to summarize
            array_push( $posts_to_summarize, $post_array );
            $posts_to_summarize = summarizes_prepare_arrays_for_summary( $posts_to_summarize, [ $the_post ], true );
            summarizes_add_summary_to_post_meta( $the_post, $posts_to_summarize );
            summarizes_log( "resummarize_updated_post post_ID=\"" . $post_ID . "\"", false );
            return TRUE;
        } else {
            summarizes_log( "resummarize_updated_post single skipping due to size post=" . $the_post->ID . " permalink=" . get_the_permalink( $the_post ) . " size=" . strlen( $new_blog_post ), false );
            return FALSE;
        }
    
    }
    
    add_action(
        'publish_post',
        'summarize_single_post',
        10,
        3
    );
}

if ( !function_exists( 'summarizes_prepare_post_array' ) ) {
    // Prepares an individual post to add it to the eventual JSON
    function summarizes_prepare_post_array( WP_Post $the_post, string $the_content ) : array
    {
        // Get the array ready to put into JSON
        // Remove all shortcodes and HTML from the content to shorten
        $strippedcontent = summarizes_strip_content( $the_content );
        $post_array = [
            "text" => [ $strippedcontent ],
            "url"  => get_the_permalink( $the_post ),
        ];
        return $post_array;
    }

}
if ( !function_exists( 'summarizes_strip_content' ) ) {
    function summarizes_strip_content( string $the_content ) : string
    {
        // Remove all shortcodes and HTML from the content to shorten
        return strip_tags( strip_shortcodes( $the_content ) );
    }

}
if ( !function_exists( 'summarizes_get_tier' ) ) {
    // Gets Freemius pay tier
    function summarizes_get_tier() : string
    {
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            return "pro";
        } else {
            return "free";
        }
    
    }

}
if ( !function_exists( 'summarizes_get_summary_type' ) ) {
    // Gets summary type
    function summarizes_get_summary_type() : string
    {
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            return get_option( 'summarizes_summary_type', 'executive' );
        } else {
            return "executive";
        }
    
    }

}
if ( !function_exists( 'summarizes_get_license' ) ) {
    // Gets Freemius licence key
    function summarizes_get_license() : string
    {
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            return summarizes_fs()->_get_license()->secret_key;
        } else {
            return "free";
        }
    
    }

}
if ( !function_exists( 'summarizes_prepare_arrays_for_summary' ) ) {
    // Prepares posts for summary, adds license information,
    // and calls the REST interface
    function summarizes_prepare_arrays_for_summary( array $post_array, array $posts, bool $force ) : array
    {
        $license_array = [
            "tier"    => summarizes_get_tier(),
            "license" => summarizes_get_license(),
        ];
        $options_array = [
            "summarytype" => summarizes_get_summary_type(),
        ];
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            $hasAllSummaries = !$force;
            $hasAllFAQs = !$force;
            $hasAllPullQuotes = !$force;
            // Check if each post has all of information in it already
            if ( $force == false ) {
                foreach ( $posts as $post ) {
                    $post_summary_array = get_post_meta( get_post()->ID, "summarizes_summary", true );
                    $summarizes_faq_array = get_post_meta( get_post()->ID, "summarizes_faq", true );
                    $summarizes_pull_quote_array = get_post_meta( get_post()->ID, "summarizes_pull_quote", true );
                    if ( $post_summary_array == "" ) {
                        $hasAllSummaries = false;
                    }
                    if ( $summarizes_faq_array == "" ) {
                        $hasAllFAQs = false;
                    }
                    if ( $summarizes_pull_quote_array == "" ) {
                        $hasAllPullQuotes = false;
                    }
                }
            }
            // Check if summary is already done
            if ( $hasAllSummaries == true ) {
                unset( $options_array['summarytype'] );
            }
            // Add faq and quotes if enabled and missing
            if ( get_option( 'summarizes_faq_enable', true ) && $hasAllFAQs == false ) {
                $options_array["faq"] = true;
            }
            
            if ( get_option( 'summarizes_pull_quote_enable', true ) && $hasAllPullQuotes == false ) {
                $options_array["quotes"] = true;
                // Check if it should skip the posts with block quotes already
                $pull_quote_skip_existing = get_option( 'summarizes_pull_quote_skip_existing', true );
                
                if ( $pull_quote_skip_existing == true ) {
                    // Check if the pull quote begin html is in the post
                    $summarizes_pull_quote_begin_html = get_option( 'summarizes_pull_quote_begin_html', '<blockquote class="right quote">' );
                    foreach ( $post_list as $post_array ) {
                        
                        if ( str_contains( $post_list, $summarizes_pull_quote_begin_html ) ) {
                            // Begin html found. Skip it.
                            $options_array["quotes"] = false;
                            summarizes_log( "skipping creating pull quotes as begin html is in it", false );
                            break;
                        }
                    
                    }
                }
            
            }
        
        }
        
        $summary_array = [
            "posts"   => $post_array,
            "options" => $options_array,
            "license" => $license_array,
        ];
        summarizes_log( "summarizes_prepare_arrays_for_summary submission to REST summary_array=" . summarizes_implode_recursive( $summary_array ), false );
        $json_response = summarizes_call_summarizer( $summary_array );
        return $json_response;
    }

}
if ( !function_exists( 'summarizes_add_summary_to_post_meta' ) ) {
    // Adds the summary and method to the post
    function summarizes_add_summary_to_post_meta( WP_Post $the_post, array $posts_to_summarize )
    {
        $permalink = get_the_permalink( $the_post );
        // Verify there wasn't an issue finding the post's summary
        
        if ( array_key_exists( $permalink, $posts_to_summarize["summarydetails"] ) == false ) {
            // There was an error with finding the page's summary
            summarizes_log( "summarizes_add_summary_to_post_meta could not find permalink {$permalink} in posts_to_summarize=\"" . summarizes_implode_recursive( $posts_to_summarize ) . "\"", false );
        } else {
            
            if ( array_key_exists( 'summaryresult', $posts_to_summarize["summarydetails"][$permalink] ) ) {
                // Add the summary to the post
                $post_summary_array = $posts_to_summarize["summarydetails"][$permalink]["summaryresult"];
                $post_method = $posts_to_summarize["summarydetails"][$permalink]["summarymethod"];
                $worked1 = update_post_meta( $the_post->ID, "summarizes_summary", $post_summary_array );
                $worked2 = update_post_meta( $the_post->ID, "summarizes_method", $post_method );
                summarizes_log( "summarizes_add_summary_to_post_meta post_summary_array=\"" . summarizes_implode_recursive( $post_summary_array ) . "\" worked1=\"" . summarizes_bool_to_string( $worked1 ) . "\" worked2=\"" . summarizes_bool_to_string( $worked2 ) . "\"", false );
            }
            
            
            if ( summarizes_fs()->can_use_premium_code() ) {
                
                if ( array_key_exists( 'quotesresult', $posts_to_summarize["summarydetails"][$permalink] ) ) {
                    // Add the pull quotes to the post
                    $quotes_array = $posts_to_summarize["summarydetails"][$permalink]['quotesresult'];
                    $worked1 = update_post_meta( $the_post->ID, "summarizes_pull_quote", $quotes_array );
                    summarizes_log( "summarizes_add_summary_to_post_meta quotes_array=\"" . summarizes_implode_recursive( $quotes_array ) . "\" worked1=\"" . summarizes_bool_to_string( $worked1 ) . "\"", false );
                }
                
                
                if ( array_key_exists( 'faqresult', $posts_to_summarize["summarydetails"][$permalink] ) ) {
                    // Add the faq to the post
                    $faq_array = $posts_to_summarize["summarydetails"][$permalink]['faqresult'];
                    $worked1 = update_post_meta( $the_post->ID, "summarizes_faq", $faq_array );
                    summarizes_log( "summarizes_add_summary_to_post_meta faq_array=\"" . summarizes_implode_recursive( $faq_array ) . "\" worked1=\"" . summarizes_bool_to_string( $worked1 ) . "\"", false );
                }
            
            }
        
        }
    
    }

}
if ( !function_exists( 'summarizes_implode_recursive' ) ) {
    // Implodes an array into JSON string
    function summarizes_implode_recursive( $array ) : string
    {
        
        if ( is_string( $array ) ) {
            return $array;
        } else {
            return wp_json_encode( $array );
        }
    
    }

}
if ( !function_exists( 'summarizes_bool_to_string' ) ) {
    // Converts a bool a string
    function summarizes_bool_to_string( bool $boolean ) : string
    {
        return ( $boolean ? 'true' : 'false' );
    }

}
if ( !function_exists( 'summarizes_better_summary_exists' ) ) {
    // Retrieves the summarization model and return if a better model's summary is available
    // Returns true if a better model exists
    function summarizes_better_summary_exists( WP_Post $the_post ) : bool
    {
        $summarizes_method = get_post_meta( $the_post->ID, "summarizes_method", true );
        $tier = summarizes_get_tier();
        
        if ( summarizes_fs()->can_use_premium_code() ) {
            if ( summarizes_check_pro_better_summary_exists__premium_only( $tier, $summarizes_method ) == false ) {
                if ( summarizes_check_faq_and_pull_quote_exist__premium_only( $the_post ) == true ) {
                    // Summary there but missing faq or pull quote
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    
    }

}
if ( !function_exists( 'summarizes_check_pro_better_summary_exists__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_check_faq_and_pull_quote_exist__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_call_summarizer' ) ) {
    // Calls the summarizer REST API and handles calls to
    // server and server-side errors
    function summarizes_call_summarizer( array $summary_array ) : array
    {
        // Production GCP
        $url = 'https://api.summariz.es/api/v1';
        // Define the request parameters.
        $args = [
            'method'  => 'POST',
            'headers' => [
            'Content-Type' => 'application/json',
        ],
            'body'    => wp_json_encode( $summary_array ),
            'timeout' => 90,
        ];
        // Send the request.
        $response = wp_remote_post( $url, $args );
        // Check for errors and parse the response body.
        
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo  '<div class="notice notice-error">Something went wrong while trying to summarize: ' . wp_kses( $error_message, wp_kses_allowed_html( 'strip' ) ) . '</div>' ;
            summarizes_log( "Received error response from server {$error_message}", true );
            return [];
        } else {
            // Server responded, now check for server-side error
            $response_body = wp_remote_retrieve_body( $response );
            $response_data = json_decode( $response_body, true );
            
            if ( $response_data["summarydetails"]["result"] != "success" ) {
                // There was a server-side failure of some kind
                echo  '<div class="notice notice-error">Something went wrong communicating with the server: ' . wp_kses( $response_data["summarydetails"]["message"], wp_kses_allowed_html( 'strip' ) ) . '</div>' ;
                summarizes_log( "Something went wrong on the server \"{$response_body}\"", true );
                return [];
            } else {
                // Summary worked, return summaries
                summarizes_log( "summarizes_call_summarizer Summary fine. response_data=\"{$response_body}\"", false );
                return $response_data;
            }
        
        }
    
    }

}
if ( !function_exists( 'summarizes_log' ) ) {
    // Logs out summary AI trace information and may log to main
    // Wordpress logging
    function summarizes_log( string $str, bool $log_to_main_error )
    {
        
        if ( get_option( 'summarizes_debug_mode', false ) == true ) {
            $d = date( "j-M-Y H:i:s e" );
            error_log( "[{$d}] {$str}\n", 3, plugin_dir_path( __FILE__ ) . "summarizes.log" );
        }
        
        if ( $log_to_main_error == true ) {
            error_log( $str );
        }
    }

}
add_action( 'admin_post_batch_summarizes', 'summarizes_batch_summarizes__premium_only' );
if ( !function_exists( 'summarizes_batch_summarizes__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_batch_summarizes_query__premium_only' ) ) {
}
if ( !function_exists( 'summarizes_batch_bulk_summarizes_notice__premium_only' ) ) {
    add_action( 'admin_notices', 'summarizes_batch_bulk_summarizes_notice__premium_only' );
}