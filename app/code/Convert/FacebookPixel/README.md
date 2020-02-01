# Convert_FacebookPixel

Facebook Pixel is not tracking the AddToCart events because the value is missing in the event data.

`   fbq('track', 'AddToCart', {
      content_ids: ['1234'],
      content_type: 'product',
      value: 2.99,
      currency: 'USD' 
    });`

Solution: Rewrite Bss\FacebookPixel to fix the issue with the data

_Jira Ticket: MAKSD-196_