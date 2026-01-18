using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public interface IEncyclopediaSeriesViewDelegate
    {
        void OnViewDidLoad();
        void SelectUnitList();
        void SelectCollectionList();
        void OnSelectPlayerUnit(MasterDataId mstUnitId, EncyclopediaUnlockFlag isUnlocked);
        void OnSelectEnemyUnit(MasterDataId mstEnemyId, EncyclopediaUnlockFlag isUnlocked);
        void OnSelectArtwork(MasterDataId mstArtworkId);
        void OnSelectEmblem(MasterDataId mstEmblemId);
        void OnBackCloseButtonTapped();
        void OnHomeButtonTapped();
        void OnShowJumpPlusButtonTapped();
    }
}
