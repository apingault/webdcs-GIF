#!/bin/bash

# Set RUN to HVSCAN
echo 'HVSCAN' > /var/operation/RUN_STABILITY/run

# Generate HVSCAN in database
#php /home/webdcs/software/webdcs/public_html/test/cronJob.php

# Run HVSCAN program


# Update RUN to RUN
echo 'RUN' > /var/operation/RUN_STABILITY/run

