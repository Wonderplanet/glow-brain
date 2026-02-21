using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleEndResultModelTranslator
    {
        public static AdventBattleEndResultModel ToAdventBattleEndResultModel(AdventBattleEndResultData data)
        {
            var userItems = data.UsrItems.Select(
                ItemDataTranslator.ToUserItemModel).ToList();
            
            var userDiscoveries = data.UsrEnemyDiscoveries.Select(
                UserEnemyDiscoverDataTranslator.Translate).ToList();
            
            var dropRewards = data.AdventBattleDropRewards.Select(
                AdventBattleRewardModelTranslator.ToAdventBattleRewardModel).ToList();
            
            var rankRewards = data.AdventBattleRankRewards.Select(
                AdventBattleRewardModelTranslator.ToAdventBattleRewardModel).ToList();

            var clearRewards = data.AdventBattleClearRewards.Select(
                AdventBattleClearRewardModelTranslator.ToAdventBattleClearRewardModel).ToList();

            var conditionPacks = data.UsrConditionPacks.Select(
                UserConditionPackDataTranslator.ToModel).ToList();
            
            return new AdventBattleEndResultModel(
                UserAdventBattleModelTranslator.ToUserAdventBattleModel(data.UsrAdventBattle),
                new AdventBattleRaidTotalScore(data.TotalDamage),
                UserParameterTranslator.ToUserParameterModel(data.UsrParameter),
                UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel),
                userItems,
                userDiscoveries,
                dropRewards,
                rankRewards,
                clearRewards,
                conditionPacks
            );
        }
    }
}