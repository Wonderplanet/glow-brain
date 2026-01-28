using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBuyStaminaAdDuringIntervalException : DataInconsistencyServerErrorException
    {
        public UserBuyStaminaAdDuringIntervalException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
