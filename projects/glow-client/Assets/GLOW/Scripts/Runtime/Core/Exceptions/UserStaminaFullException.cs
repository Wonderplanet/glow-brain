using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserStaminaFullException : DataInconsistencyServerErrorException
    {
        public UserStaminaFullException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
