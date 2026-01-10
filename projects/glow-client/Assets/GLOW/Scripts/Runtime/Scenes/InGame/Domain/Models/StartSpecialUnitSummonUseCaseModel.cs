using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record StartSpecialUnitSummonUseCaseModel(
        CanSpecialUnitSummonFlag CanSpecialUnitSummonFlag,
        SpecialUnitSummonKomaRange SpecialUnitSummonKomaRange,
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary,
        IReadOnlyList<KomaId> SummoningKomaIds,
        NeedTargetSelectTypeFlag NeedTargetSelectTypeFlag)
    {
        public static StartSpecialUnitSummonUseCaseModel Empty { get; } = new (
            CanSpecialUnitSummonFlag.False,
            SpecialUnitSummonKomaRange.Empty,
            new Dictionary<KomaId, KomaModel>(),
            new List<KomaId>(),
            NeedTargetSelectTypeFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
