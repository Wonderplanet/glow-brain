using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageEventPartyViolationRuleException : WrappedServerErrorException
    {
        public StageEventPartyViolationRuleException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
