using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Domain.Modules.Network
{
    public static class NetworkErrorExceptionMessage
    {
        static readonly List<string> NetworkErrorMessages = new()
        {
            "Cannot connect to destination host",
            "Failed to receive data",
            "Cannot resolve destination host",
            "Unable to complete SSL connection"
        };
        
        public static bool IsNetworkErrorExceptionMessage(string exceptionMessage)
        {
            if (string.IsNullOrEmpty(exceptionMessage)) return false;

            return NetworkErrorMessages.Any(errorMessage => exceptionMessage == errorMessage);
        }
    }
}