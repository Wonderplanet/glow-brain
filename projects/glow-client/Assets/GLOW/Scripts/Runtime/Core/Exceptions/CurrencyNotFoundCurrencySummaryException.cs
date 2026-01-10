using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class CurrencyNotFoundCurrencySummaryException : WrappedServerErrorException
    {
        public CurrencyNotFoundCurrencySummaryException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
