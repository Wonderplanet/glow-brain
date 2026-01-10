using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBnidNotLinkedException : WrappedServerErrorException
    {
        public UserBnidNotLinkedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
