using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBnidAccessTokenApiErrorException : WrappedServerErrorException
    {
        public UserBnidAccessTokenApiErrorException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
