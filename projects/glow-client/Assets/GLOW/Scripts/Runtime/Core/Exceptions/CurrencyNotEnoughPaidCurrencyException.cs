using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyNotEnoughPaidCurrencyException : DataInconsistencyServerErrorException
    {
        public CurrencyNotEnoughPaidCurrencyException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
