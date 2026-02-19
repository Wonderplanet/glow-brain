using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UnitCannotResetLevelException : WrappedServerErrorException
    {
        public UnitCannotResetLevelException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
