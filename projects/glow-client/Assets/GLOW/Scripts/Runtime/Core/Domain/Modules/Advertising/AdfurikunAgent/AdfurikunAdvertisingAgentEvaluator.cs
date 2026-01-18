using System.Text.RegularExpressions;

namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public static class AdfurikunAdvertisingAgentEvaluator
    {
        public static bool IsValidAppID(string appId)
        {
            return Regex.IsMatch(appId, @"^[a-f0-9]{24}$");
        }
    }
}
