using System;

namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public static class IAARewardFeatureTypeExtensions
    {
        public static string ToAdfurikunCustomParamValue(this IAARewardFeatureType type)
        {
            return type switch
            {
                IAARewardFeatureType.Shop => AdfurikunCustomParamConst.ValueShop,
                IAARewardFeatureType.IdleIncentive => AdfurikunCustomParamConst.ValueIdleIncentive,
                IAARewardFeatureType.Gacha => AdfurikunCustomParamConst.ValueGacha,
                IAARewardFeatureType.StaminaRecover => AdfurikunCustomParamConst.ValueStamina,
                IAARewardFeatureType.Continue => AdfurikunCustomParamConst.ValueContinue,
                IAARewardFeatureType.QuestChallenge => AdfurikunCustomParamConst.ValueQuestChallenge,
                _ => throw new ArgumentOutOfRangeException(nameof(type), type, null),
            };
        }
    }
}