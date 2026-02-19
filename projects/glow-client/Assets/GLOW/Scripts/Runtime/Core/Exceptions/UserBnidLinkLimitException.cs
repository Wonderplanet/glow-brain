using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBnidLinkLimitException : WrappedServerErrorException
    {
        public UserBnidLinkLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
