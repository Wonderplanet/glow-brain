using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UnitLevelUpExceedLimitLevelException : DataInconsistencyServerErrorException
    {
        public UnitLevelUpExceedLimitLevelException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
