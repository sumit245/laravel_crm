tested and working fine:
1. All stakeholder change/replacement -Done
2. Show data District wise>Block wise>Panchayat to avoid lookups -Done
3. Add/update/delete image of pole -Done
7. Add Scope quantity in panchayat import -Done
9. Download Inventory Format -Done
13. Dispatch multiple items from excel with download format -Done
15. Extend date of target instead of multiple targets -Done
21. Add Beneficiary and other columns while export table -Done
26. Remove staff from staff management itself -Done
27. Multiple edit delete -Done


not working properly:

4. Project wise backups, right now its global -Done
> Able to create project wise backups. in backup data found data mismatch in actual data vs backed up data. (sheet shared with mr. sumit for verification)

10. Inventory Locking -Done
> Inventory lock working while dispatching. but fails when editing pole data as it allows item from from other inventory and updates pole details.

12. Add SIM column for luminary item to avoid duplicity -Done
> SIM column is not showing in inventory. Replacing IMEI is not automatically changing SIM as every SIM is defined for specific IMEI at the time of add inventory.

14. Add Target ward wise -Done
> Currently add target is not working. already shared screenshot of error display.

16. JICR creation by panchayat only -Done
> JICR creation successful. Print format is not accurate as it is overlapping texts and table. need to fix.

17. Add GP in import site -Done 
> Adding "GP" is currently possible with import only. Editing or manually adding site doesn't allow "GP" as ward.

20. Add new site codes (district code, block code, panchayat code) -Done
> how can i check if codes for district, block, panchayat is added?

22. Edit Pole should also update inventory -Done
> installed pole column is not there in inventory to verify if it updated/synced properly with inventory.