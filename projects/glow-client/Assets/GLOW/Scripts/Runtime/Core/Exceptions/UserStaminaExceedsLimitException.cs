using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserStaminaExceedsLimitException : WrappedServerErrorException
    {
        public UserStaminaExceedsLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
