using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyNotEnoughCurrencyException : DataInconsistencyServerErrorException
    {
        public CurrencyNotEnoughCurrencyException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
