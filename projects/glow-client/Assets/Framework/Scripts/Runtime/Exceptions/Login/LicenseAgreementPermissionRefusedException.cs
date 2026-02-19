using System;

namespace WPFramework.Exceptions
{
    public sealed class LicenseAgreementPermissionRefusedException : ApplicationException
    {
        public LicenseAgreementPermissionRefusedException()
        {
        }

        public LicenseAgreementPermissionRefusedException(string message) : base(message)
        {
        }
    }
}
