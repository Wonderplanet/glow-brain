using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBuyStaminaCountLimitException : DataInconsistencyServerErrorException
    {
        public UserBuyStaminaCountLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
