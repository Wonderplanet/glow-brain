using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Extensions
{
    public static class InGameCommonConditionTypeExtension
    {
        public static bool IsEnemyOutpostDamageCondition(this InGameCommonConditionType conditionType)
        {
            return conditionType 
                is InGameCommonConditionType.EnemyOutpostDamage
                or InGameCommonConditionType.EnemyOutpostHpPercentage;
        }
    }
}