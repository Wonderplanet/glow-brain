using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.InGame.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public class AdventBattleInGameEndBattleLogModelFactory : IAdventBattleInGameEndBattleLogModelFactory
    {
        [Inject] IInGameLogRepository InGameLogRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPartyStatusModelFactory PartyStatusModelFactory { get; }
        [Inject] IInGameScene InGameScene { get; }

        InGameEndBattleLogModel IAdventBattleInGameEndBattleLogModelFactory.CreateInGameEndBattleLogModel(
            IReadOnlyList<UserUnitModel> userUnitModels,
            StageClearTime stageClearTime)
        {
            var inGameLog = InGameLogRepository.GetLog();

            var partyStatusModels = PartyCacheRepository
                .GetCurrentPartyModel()
                .GetUnitList()
                .Join(userUnitModels, id => id, model => model.UsrUnitId, (_, model) => model)
                .Select(m => PartyStatusModelFactory.CreatePartyStatusModel(
                    m,
                    InGameType.AdventBattle,
                    MasterDataId.Empty, // 降臨バトルではEventBonusGroupIdを使用するのでクエストIDは不要
                    InGameScene.EventBonusGroupId,
                    InGameScene.SpecialRuleUnitStatusModels))
                .ToList();

            return new InGameEndBattleLogModel(
                inGameLog.DefeatEnemyCount,
                inGameLog.DefeatBossEnemyCount,
                InGameScene.ScoreModel.TotalScore,
                partyStatusModels,
                stageClearTime,
                inGameLog.MaxDamage,
                inGameLog.DiscoveredMstEnemyCharacterIds
            );
        }
    }
}
