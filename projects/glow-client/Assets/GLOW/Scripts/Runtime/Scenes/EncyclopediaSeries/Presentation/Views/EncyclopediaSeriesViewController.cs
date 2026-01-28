using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-2_作品別TOP画面
    /// 　　91-2-1-1_作品別TOP画面タブ
    /// 　　91-2-2_作品別キャラ一覧TOP画面
    /// 　　91-2-3_作品別コレクションTOP画面
    /// 　　　91-2-3-1_作品別原画一覧
    /// 　　　91-2-3-2_作品別エンブレム一覧
    /// </summary>
    public class EncyclopediaSeriesViewController : HomeBaseViewController<EncyclopediaSeriesView>
    {
        public record Argument(MasterDataId MstSeriesId);

        [Inject] IEncyclopediaSeriesViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        public void SetupLogo(SeriesLogoImagePath path, SeriesIconImagePath seriesIconImagePath)
        {
            ActualView.SetContentInfo(path, seriesIconImagePath);
        }

        public void SetUnitTabBadge(NotificationBadge badge)
        {
            ActualView.SetUnitBadge(badge);
        }

        public void SetCollectionTabBadge(NotificationBadge badge)
        {
            ActualView.SetCollectionBadge(badge);
        }

        public void SetupUnitList(EncyclopediaSeriesUnitListViewModel viewModel)
        {
            ActualView.SetupUnitList(viewModel, ViewDelegate.OnSelectPlayerUnit, ViewDelegate.OnSelectEnemyUnit);
        }

        public void SetupCollectionList(EncyclopediaSeriesCollectionListViewModel viewModel)
        {
            ActualView.SetupCollectionList(viewModel, ViewDelegate.OnSelectArtwork, ViewDelegate.OnSelectEmblem);
        }
        
        public void ShowCharacterList()
        {
            ActualView.ShowCharacterList();
        }
        
        public void ShowCollectionList()
        {
            ActualView.ShowCollectionList();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnBackCloseButtonTapped();
        }

        [UIAction]
        void OnHomeButtonTapped()
        {
            ViewDelegate.OnHomeButtonTapped();
        }

        [UIAction]
        void OnShowJumpPlusButtonTapped()
        {
            ViewDelegate.OnShowJumpPlusButtonTapped();
        }

        [UIAction]
        void OnCharaTabButtonTapped()
        {
            ViewDelegate.SelectUnitList();
        }

        [UIAction]
        void OnCollectionTabButtonTapped()
        {
            ViewDelegate.SelectCollectionList();
        }
    }
}
