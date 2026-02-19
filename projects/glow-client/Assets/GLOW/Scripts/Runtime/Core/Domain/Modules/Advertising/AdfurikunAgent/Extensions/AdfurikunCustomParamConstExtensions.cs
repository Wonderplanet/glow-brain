using System.Collections.Generic;

namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public static class AdfurikunCustomParamConstExtensions
    {
        public static Dictionary<string, string> CreateCustomParam(IAARewardFeatureType type)
        {
            return new Dictionary<string, string>
            {
                {AdfurikunCustomParamConst.Key, type.ToAdfurikunCustomParamValue()}
            };
        }
    }
}