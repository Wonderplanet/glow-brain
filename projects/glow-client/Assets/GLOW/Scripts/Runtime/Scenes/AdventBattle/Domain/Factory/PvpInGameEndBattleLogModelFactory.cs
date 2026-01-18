using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.InGame.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public class PvpInGameEndBattleLogModelFactory : IPvpInGameEndBattleLogModelFactory
    {
        [Inject] IInGameLogRepository InGameLogRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IPartyStatusModelFactory PartyStatusModelFactory { get; }

        PvpInGameEndBattleLogModel IPvpInGameEndBattleLogModelFactory.CreateInGameEndBattleLogModel(
            IReadOnlyList<UserUnitModel> userUnitModels,
            StageClearTime currentTickCount,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var partyStatusModels = PartyCacheRepository
                .GetCurrentPartyModel()
                .GetUnitList()
                .Join(userUnitModels, id => id, model => model.UsrUnitId, (_, model) => model)
                .Select(m => PartyStatusModelFactory.CreatePartyStatusModel(
                    m,
                    InGameType.Pvp,
                    MasterDataId.Empty,
                    EventBonusGroupId.Empty, // Pvpではイベントボーナスは無いはず
                    specialRuleUnitStatusModels))
                .ToList();

            var opponentStatusModel = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();

            var opponentPartyStatusModels = opponentStatusModel.PvpUnits
                .Select(pvpUnit => PartyStatusModelFactory.CreatePartyStatusModel(
                    pvpUnit,
                    specialRuleUnitStatusModels))
                .ToList();

            return new PvpInGameEndBattleLogModel(
                currentTickCount,
                InGameLogRepository.GetLog().MaxDamage,
                partyStatusModels,
                opponentPartyStatusModels);
        }
    }
}
