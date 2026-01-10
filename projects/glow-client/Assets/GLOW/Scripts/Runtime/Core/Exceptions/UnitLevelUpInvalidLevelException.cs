using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UnitLevelUpInvalidLevelException : DataInconsistencyServerErrorException
    {
        public UnitLevelUpInvalidLevelException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
