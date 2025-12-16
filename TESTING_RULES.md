# Testing Rules and Guidelines

## Browser Testing Requirements

### When Browser Testing is Required

1. **Always test in browser when:**

    - User explicitly requests testing via `@Browser` or mentions testing a specific URL
    - Fixing bugs related to form submissions, user interactions, or UI flows
    - Implementing new features that involve user-facing forms or workflows
    - User reports an issue that requires reproducing the exact user experience
    - Making changes to validation rules, form handling, or database constraints that affect user input

2. **Browser testing process:**

    - Navigate to the exact URL provided by the user (user will provide the URL)
    - Log in with provided credentials if authentication is required
    - Fill out forms with realistic test data matching the user's described scenario
    - Test all possible input combinations mentioned by the user:
        - Different dropdown selections (e.g., "Other" options)
        - Existing user selections
        - New user/participant additions
        - All required fields
    - Submit forms and verify success or identify exact error messages
    - Check browser console for JavaScript errors
    - Check network requests to verify API calls are being made correctly
    - Verify redirects and success messages appear correctly

3. **Testing completion criteria:**
    - Form submission must complete successfully OR
    - Exact error message must be captured and displayed
    - No guesswork - only report what is actually observed in the browser
    - If errors occur, capture exact error messages, Flare links, or console errors

## Communication Rules - No Guess Words

### Prohibited Phrases (DO NOT USE):

-   ❌ "It should work"
-   ❌ "There may be some problem"
-   ❌ "It might work"
-   ❌ "Probably"
-   ❌ "Likely"
-   ❌ "I think"
-   ❌ "Perhaps"
-   ❌ "Maybe"
-   ❌ "Should be fine"
-   ❌ "Might need to"
-   ❌ "Could be"

### Required Communication Style:

-   ✅ "DONE" - When testing confirms functionality works
-   ✅ "NOT DONE" - When testing reveals issues
-   ✅ "Tested and verified: [exact result]" - When browser testing is complete
-   ✅ "Error observed: [exact error message]" - When errors are found
-   ✅ "Status: [specific state]" - Clear binary states only
-   ✅ Use concrete facts: "The form submitted successfully and redirected to /meets"
-   ✅ Use specific error messages: "SQLSTATE[23000]: Integrity constraint violation..."
-   ✅ Report exact browser behavior: "Page remained on /meets/create after submission"

### Binary Status Reporting:

-   Use only: **DONE** or **NOT DONE**
-   Provide concrete evidence for status:
    -   **DONE**: "Browser test completed - meeting created successfully, visible in meetings list"
    -   **NOT DONE**: "Browser test failed - error message: [exact error]"

## Testing Workflow

1. **Before declaring completion:**

    - Must test in browser if user requests it
    - Must test all scenarios mentioned by user
    - Must verify actual functionality, not assume it works
    - Must capture exact error messages if failures occur

2. **After making code changes:**

    - Test the changes in browser before declaring completion
    - Verify the fix resolves the reported issue
    - Test edge cases mentioned by user (e.g., "Other" options, new users, etc.)

3. **Error handling:**
    - If browser testing reveals errors, fix them immediately
    - Re-test after fixes
    - Continue until browser testing confirms success OR user provides new error information

## Examples

### ✅ CORRECT Response:

```
Status: DONE

Browser testing completed:
- Logged in successfully with provided credentials
- Filled form with "Other" type, custom type "Standup Meeting"
- Selected 3 existing users
- Added 1 new participant (John Doe)
- Form submitted successfully
- Redirected to /meets page
- New meeting visible in list with correct details
```

### ❌ INCORRECT Response:

```
Status: Should work now

I've made the changes and it should work. There may be some issues
with validation but it probably will be fine. You might need to test
it yourself to be sure.
```

## Summary

-   **Always test in browser when requested** - No exceptions
-   **Use binary status only** - DONE or NOT DONE
-   **Provide concrete evidence** - Exact test results, error messages, or success confirmations
-   **No guess words** - Only report what is actually observed and verified
-   **Test all scenarios** - Cover all combinations mentioned by the user
-   **Fix and re-test** - Continue until browser testing confirms success
