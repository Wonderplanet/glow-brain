using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
namespace GLOW.Core.Domain.Extensions
{
    public static class QuestTypeExtensions
    {
        public static CampaignTargetType ToCampaignTargetType(this QuestType type)
        {
            return type switch
            {
                QuestType.Normal => CampaignTargetType.NormalQuest,
                QuestType.Enhance => CampaignTargetType.EnhanceQuest,
                QuestType.Event => CampaignTargetType.EventQuest,
                _ => CampaignTargetType.NormalQuest
            };
        }
    }
}