using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameGimmickObjectFactory : IInGameGimmickObjectFactory
    {
        [Inject] IFieldObjectIdProvider FieldObjectIdProvider { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }

        public InGameGimmickObjectModel Generate(
            InGameGimmickObjectGenerationModel gimmickObjectGenerationModel,
            InGameGimmickObjectAssetKey assetKey,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            var id = FieldObjectIdProvider.GenerateNewId();
            var battleSide = BattleSide.Enemy;
            var pos = gimmickObjectGenerationModel.SummonPosition.IsEmpty()
                ? InGameConstants.DefaultSummonPos
                : CoordinateConverter.FieldToEnemyOutpostCoord(gimmickObjectGenerationModel.SummonPosition);
            var locateKomaId = page.GetKomaIdAt(CoordinateConverter.OutpostToFieldCoord(battleSide, pos));
            var locateKomaModel = komaDictionary.GetValueOrDefault(locateKomaId, KomaModel.Empty);

            return new InGameGimmickObjectModel(
                id,
                gimmickObjectGenerationModel.AutoPlayerSequenceElementId,
                assetKey,
                pos,
                locateKomaModel,
                NeedsRemovalFlag.False);
        }
    }
}
