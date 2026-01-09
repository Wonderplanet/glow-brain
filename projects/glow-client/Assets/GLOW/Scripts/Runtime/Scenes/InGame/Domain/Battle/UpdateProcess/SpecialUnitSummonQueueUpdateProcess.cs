using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    /// <summary> ロールがスペシャルのユニットの召喚キュー更新対応 </summary>
    public class SpecialUnitSummonQueueUpdateProcess : ISpecialUnitSummonQueueUpdateProcess
    {
        [Inject] ISpecialUnitFactory SpecialUnitFactory { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public SpecialUnitSummonQueueUpdateProcessResult UpdateSummonQueue(
            IReadOnlyList<SpecialUnitModel> units,
            IReadOnlyList<MasterDataId> usedSpecialUnitIdsBeforeNextRush,
            SpecialUnitSummonQueueModel summonQueueModel,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            var createdSpecialUnits = summonQueueModel.SummonQueue
                .Select(queueElement => CreateSpecialUnit(queueElement, komaDictionary, page))
                .ToList();
            
            // プレイヤー側で使用されたスペシャルユニットIDの一覧
            var usedSpecialUnitIds = createdSpecialUnits
                .Where(unit => unit.BattleSide == BattleSide.Player)
                .Select(unit => unit.CharacterId)
                .ToList();
            
            var updateSpecialUnits = units.Concat(createdSpecialUnits).ToList();
            
            // 次の総攻撃までに使用されたスペシャルユニットIDの一覧を更新(重複排除)
            var updatedUsedSpecialUnitIdsBeforeNextRush = usedSpecialUnitIdsBeforeNextRush
                .Concat(usedSpecialUnitIds)
                .Distinct()
                .ToList();

            var updateSummonQueueModel = SpecialUnitSummonQueueModel.Empty;

            return new SpecialUnitSummonQueueUpdateProcessResult(
                createdSpecialUnits,
                updateSpecialUnits,
                updatedUsedSpecialUnitIdsBeforeNextRush,
                updateSummonQueueModel);
        }

        SpecialUnitModel CreateSpecialUnit(
            SpecialUnitSummonQueueElement queueElement,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            var mstCharacter = MstCharacterDataRepository.GetCharacter(queueElement.Id);

            return SpecialUnitFactory.GenerateSpecialUnit(
                mstCharacter,
                queueElement.BattleSide,
                komaDictionary,
                page,
                queueElement.Pos);
        }
    }
}
