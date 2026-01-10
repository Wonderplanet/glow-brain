using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Presentation.ViewModel
{
    public record PvpTopOpponentViewModel(
        UserMyId UserId,
        UserName UserName,
        CharacterIconAssetPath CharacterIconAssetPath,
        EmblemIconAssetPath EmblemIconAssetPath,
        PvpPoint Point,
        PvpPoint TotalPoint,
        PvpUserRankStatus PvpUserRankStatus,
        IReadOnlyList<PvpTopOpponentPartyUnitViewModel> PartyUnits,
        TotalPartyStatus TotalPartyStatus,
        TotalPartyStatusUpperArrowFlag TotalPartyStatusUpperArrowFlag)
    {
        public static PvpTopOpponentViewModel Empty { get; } = new(
            UserMyId.Empty,
            UserName.Empty,
            CharacterIconAssetPath.Empty,
            EmblemIconAssetPath.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpUserRankStatus.Empty,
            new List<PvpTopOpponentPartyUnitViewModel>(),
            TotalPartyStatus.Empty,
            TotalPartyStatusUpperArrowFlag.False
        );
    };
}
