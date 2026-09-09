const { createClient } = require('@supabase/supabase-js');

// Hostinger automatically injects SUPABASE_URL and SUPABASE_KEY (or SUPABASE_ANON_KEY) into environment variables
const supabaseUrl = process.env.SUPABASE_URL || process.env.SUPABASE_PROJECT_URL || 'https://ebhbndrhxcemceuvvkfp.supabase.co';
const supabaseKey = process.env.SUPABASE_KEY || process.env.SUPABASE_ANON_KEY || process.env.SUPABASE_SERVICE_ROLE_KEY || 'dummy_anon_key';

const supabase = createClient(supabaseUrl, supabaseKey);

async function testConnection() {
    try {
        // 'admin_accounts' is a table in the Health Delivery System database
        const { data, error } = await supabase
            .from('admin_accounts')
            .select('*')
            .limit(1);

        if (error) {
            console.error('Error connecting to Supabase:', error.message);
            return { success: false, error: error.message };
        }

        console.log('Successfully connected to Supabase! Sample data:', data);
        return { success: true, data };
    } catch (err) {
        console.error('Unexpected error:', err);
        return { success: false, error: err.message };
    }
}

// Run connection test if file is executed directly with `node db.js`
if (require.main === module) {
    testConnection();
}

module.exports = { supabase, testConnection };
