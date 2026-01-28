using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.Model
{
    public record PvpTopOpponentModel(
        UserMyId UserId,
        UserName UserName,
        CharacterIconAssetPath CharacterIconAssetPath,
        EmblemIconAssetPath EmblemIconAssetPath,
        PvpPoint Point,
        PvpPoint TotalPoint,
        PvpUserRankStatus PvpUserRankStatus,
        IReadOnlyList<PvpTopOpponentPartyUnitModel> PartyUnits,
        TotalPartyStatus TotalPartyStatus,
        TotalPartyStatusUpperArrowFlag TotalPartyStatusUpperArrowFlag)
    {
        public static PvpTopOpponentModel Empty { get; } = new(
            UserMyId.Empty,
            UserName.Empty,
            CharacterIconAssetPath.Empty,
            EmblemIconAssetPath.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpUserRankStatus.Empty,
            new List<PvpTopOpponentPartyUnitModel>(),
            TotalPartyStatus.Empty,
            TotalPartyStatusUpperArrowFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
