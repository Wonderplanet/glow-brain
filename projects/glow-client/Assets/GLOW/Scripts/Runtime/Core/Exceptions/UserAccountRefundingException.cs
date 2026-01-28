using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserAccountRefundingException : WrappedServerErrorException
    {
        public UserAccountRefundingException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}