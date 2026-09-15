const http = require('http');

http.get('http://127.0.0.1/Health-Delivery-System-Latest/Patients/index.php?barangay=alijis&service=immunization', (res) => {
  console.log('Got response status:', res.statusCode);
  let data = '';
  res.on('data', chunk => data += chunk);
  res.on('end', () => {
    console.log('Contains Relationship to Recipient:', data.includes('Relationship to Recipient'));
    console.log('Contains immunization_relationship:', data.includes('id="immunization_relationship"'));
    console.log('Contains extraRecipientFields:', data.includes('id="extraRecipientFields"'));
    console.log('Contains recipient_first_name:', data.includes('name="recipient_first_name"'));
    console.log('Contains recipient_birth_date:', data.includes('name="recipient_birth_date"'));
  });
}).on('error', (err) => {
  console.error('Connection error:', err);
});
