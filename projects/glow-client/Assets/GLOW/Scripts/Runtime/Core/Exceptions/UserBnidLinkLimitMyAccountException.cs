using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBnidLinkLimitMyAccountException : WrappedServerErrorException
    {
        public UserBnidLinkLimitMyAccountException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
