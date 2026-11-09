@echo off
title Launch Cypress Headed Visual Tests - MQT
echo =======================================================
echo   Mail Query Tracker (MQT) - Cypress E2E Visual Test
echo =======================================================
echo Running Cypress in headed mode...
cmd.exe /c "npx cypress run --headed"
pause
