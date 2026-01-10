using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBirthdateAlreadyRegisteredException : DataInconsistencyServerErrorException
    {
        public UserBirthdateAlreadyRegisteredException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
