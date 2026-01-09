using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ItemTradeAmountLimitExceededException : DataInconsistencyServerErrorException
    {
        public ItemTradeAmountLimitExceededException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
