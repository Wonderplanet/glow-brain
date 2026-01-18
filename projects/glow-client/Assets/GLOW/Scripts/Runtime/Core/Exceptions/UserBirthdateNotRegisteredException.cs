using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBirthdateNotRegisteredException : WrappedServerErrorException
    {
        public UserBirthdateNotRegisteredException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
