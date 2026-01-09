using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ExchangeNotTradePeriod : WrappedServerErrorException
    {
        public ExchangeNotTradePeriod(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
