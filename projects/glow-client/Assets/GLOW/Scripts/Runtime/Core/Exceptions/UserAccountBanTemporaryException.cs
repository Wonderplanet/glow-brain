using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserAccountBanTemporaryException : WrappedServerErrorException
    {
        public UserAccountBanTemporaryException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
