using System;
using System.Collections.Generic;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.Models;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Enum;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaSeries.Presentation.Views;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Presenters
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
    public class EncyclopediaSeriesPresenter : IEncyclopediaSeriesViewDelegate
    {
        [Inject] EncyclopediaSeriesViewController ViewController { get; }
        [Inject] EncyclopediaSeriesViewController.Argument Argument { get; }
        [Inject] GetEncyclopediaSeriesUnitListUseCase GetEncyclopediaSeriesUnitListUseCase { get; }
        [Inject] GetEncyclopediaSeriesCollectionListUseCase GetEncyclopediaSeriesCollectionListUseCase { get; }
        [Inject] GetEncyclopediaSeriesInfoUseCase GetEncyclopediaSeriesInfoUseCase { get; }
        [Inject] GetEncyclopediaSeriesUrlUseCase GetEncyclopediaSeriesUrlUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        
        EncyclopediaSeriesUnitListModel _unitListModel;
        EncyclopediaSeriesCollectionListModel _collectionListModel;
        
        EncyclopediaSeriesTabType _currentTabType = EncyclopediaSeriesTabType.Unit;

        void IEncyclopediaSeriesViewDelegate.OnViewDidLoad()
        {
            var path = GetEncyclopediaSeriesInfoUseCase.GetSeriesInfo(Argument.MstSeriesId);
            ViewController.SetupLogo(path.logo, path.icon);

            _collectionListModel = GetEncyclopediaSeriesCollectionListUseCase.GetCollectionList(Argument.MstSeriesId);
            var artworkBadge = _collectionListModel.ArtworkList
                .Any(cell => cell.IsNew);
            var emblemBadge = _collectionListModel.EmblemList
                .Any(cell => cell.IsNew);
            var collectionTabBadge = new NotificationBadge(artworkBadge || emblemBadge);
            ViewController.SetCollectionTabBadge(collectionTabBadge);
            UpdateUnitList();

            ViewController.ShowCharacterList();
        }

        void IEncyclopediaSeriesViewDelegate.SelectUnitList()
        {
            // 既にUnitタブが選択されている場合は何もしない
            if (_currentTabType == EncyclopediaSeriesTabType.Unit) return;
            
            _currentTabType = EncyclopediaSeriesTabType.Unit;
            UpdateUnitList();
            ViewController.ShowCharacterList();
        }

        void IEncyclopediaSeriesViewDelegate.SelectCollectionList()
        {
            // 既にCollectionタブが選択されている場合は何もしない
            if (_currentTabType == EncyclopediaSeriesTabType.Collection) return;
            
            _currentTabType = EncyclopediaSeriesTabType.Collection;
            
            UpdateCollectionList();
            ViewController.ShowCollectionList();
        }

        void IEncyclopediaSeriesViewDelegate.OnSelectPlayerUnit(MasterDataId mstUnitId, EncyclopediaUnlockFlag isUnlocked)
        {
            if (!isUnlocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast("未獲得のキャラになります");
                return;
            }

            var playerUnitIds = _unitListModel.PlayerUnits
                .Where(model => model.IsUnlocked.Value)
                .Select(model => model.MstUnitId)
                .ToList();
            var argument = new EncyclopediaUnitDetailViewController.Argument(playerUnitIds, mstUnitId);
            var viewController = ViewFactory.Create<
                EncyclopediaUnitDetailViewController,
                EncyclopediaUnitDetailViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);

            WaitUntilViewDestroyed(viewController, UpdateUnitList);
        }

        void IEncyclopediaSeriesViewDelegate.OnSelectEnemyUnit(
            MasterDataId mstEnemyCharacterId,
            EncyclopediaUnlockFlag isUnlocked)
        {
            if (!isUnlocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast("未発見のファントムになります");
                return;
            }

            var enemyUnitIds = _unitListModel.EnemyUnits
                .Where(model => model.IsUnlocked.Value)
                .Select(model => model.MstEnemyId)
                .ToList();
            var argument = new EncyclopediaEnemyDetailViewController.Argument(enemyUnitIds, mstEnemyCharacterId);
            var viewController = ViewFactory.Create<
                EncyclopediaEnemyDetailViewController,
                EncyclopediaEnemyDetailViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);

            WaitUntilViewDestroyed(viewController, UpdateUnitList);
        }

        void IEncyclopediaSeriesViewDelegate.OnSelectArtwork(MasterDataId mstArtworkId)
        {
            var artworks = _collectionListModel.ArtworkList
                .Select(model => model.MstArtworkId)
                .ToList();
            var argument = new EncyclopediaArtworkDetailViewController.Argument(artworks, mstArtworkId);
            var viewController = ViewFactory.Create<
                EncyclopediaArtworkDetailViewController,
                EncyclopediaArtworkDetailViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);

            WaitUntilViewDestroyed(viewController, UpdateCollectionList);
        }

        void IEncyclopediaSeriesViewDelegate.OnSelectEmblem(MasterDataId mstEmblemId)
        {
            var argument = new EncyclopediaEmblemDetailViewController.Argument(mstEmblemId);
            var viewController = ViewFactory.Create<
                EncyclopediaEmblemDetailViewController,
                EncyclopediaEmblemDetailViewController.Argument>(argument);
            ViewController.PresentModally(viewController);

            WaitUntilViewDestroyed(viewController, UpdateCollectionList);
        }

        void WaitUntilViewDestroyed(UIViewController vc, Action onDestroy)
        {
            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                await UniTask.WaitUntil(vc.View.IsDestroyed, cancellationToken: cancellationToken);
                onDestroy?.Invoke();
            });
        }

        void IEncyclopediaSeriesViewDelegate.OnBackCloseButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IEncyclopediaSeriesViewDelegate.OnHomeButtonTapped()
        {
            HomeFooterDelegate.BackToHome();
        }

        void IEncyclopediaSeriesViewDelegate.OnShowJumpPlusButtonTapped()
        {
            var url = GetEncyclopediaSeriesUrlUseCase.GetJumpPlusUrl(Argument.MstSeriesId);
            CustomOpenURL.OpenURL(url.Value);
        }

        void UpdateUnitList()
        {
            _unitListModel = GetEncyclopediaSeriesUnitListUseCase.GetUnitList(Argument.MstSeriesId);
            var unitBadge = _unitListModel.PlayerUnits
                .Any(cell => cell.IsNew);
            var enemyBadge = _unitListModel
                .EnemyUnits
                .Any(cell => cell.IsNew);
            var unitTabBadge = new NotificationBadge(unitBadge || enemyBadge);
            var unitListViewModel = TranslateToUnitListViewModel(_unitListModel);

            ViewController.SetupUnitList(unitListViewModel);
            ViewController.SetUnitTabBadge(unitTabBadge);
        }

        void UpdateCollectionList()
        {
            _collectionListModel = GetEncyclopediaSeriesCollectionListUseCase.GetCollectionList(Argument.MstSeriesId);
            var artworkBadge = _collectionListModel.ArtworkList
                .Any(cell => cell.IsNew);
            var emblemBadge = _collectionListModel.EmblemList
                .Any(cell => cell.IsNew);
            var collectionTabBadge = new NotificationBadge(artworkBadge || emblemBadge);
            var collectionListViewModel = TranslateToCollectionListViewModel(_collectionListModel);

            ViewController.SetupCollectionList(collectionListViewModel);
            ViewController.SetCollectionTabBadge(collectionTabBadge);
        }

        EncyclopediaSeriesUnitListViewModel TranslateToUnitListViewModel(EncyclopediaSeriesUnitListModel model)
        {
            var playerUnits = model.PlayerUnits
                .Select(TranslateToPlayerUnitListCell)
                .ToList();
            var enemyUnits = model.EnemyUnits
                .Select(TranslateToEnemyUnitListCell)
                .ToList();

            return new EncyclopediaSeriesUnitListViewModel(
                playerUnits,
                enemyUnits
            );
        }

        EncyclopediaPlayerUnitListCellViewModel TranslateToPlayerUnitListCell(EncyclopediaPlayerUnitListCellModel model)
        {
            return new EncyclopediaPlayerUnitListCellViewModel(
                model.MstUnitId,
                CharacterIconViewModelTranslator.Translate(model.Icon),
                model.IsUnlocked,
                model.IsNew
            );
        }

        EncyclopediaEnemyUnitListCellViewModel TranslateToEnemyUnitListCell(EncyclopediaSeriesEnemyListCellModel model)
        {
            return new EncyclopediaEnemyUnitListCellViewModel(
                model.MstEnemyId,
                EnemyIconViewModelTranslator.Translate(model.Icon),
                model.IsUnlocked,
                model.IsNew
            );
        }

        EncyclopediaSeriesCollectionListViewModel TranslateToCollectionListViewModel(EncyclopediaSeriesCollectionListModel model)
        {
            var artworkCellViewModels = model.ArtworkList
                .Select(TranslateToArtworkListCellViewModel)
                .ToList();
            var emblemCellViewModels = model.EmblemList
                .Select(TranslateToEmblemListCellViewModel)
                .ToList();

            return new EncyclopediaSeriesCollectionListViewModel(artworkCellViewModels, emblemCellViewModels);
        }

        EncyclopediaArtworkListCellViewModel TranslateToArtworkListCellViewModel(EncyclopediaArtworkListCellModel model)
        {
            return new EncyclopediaArtworkListCellViewModel(
                model.MstArtworkId,
                ArtworkPanelViewModelTranslator.ToArtworkFragmentPanelViewModel(model.ArtworkPanelModel),
                model.IsUnlocked,
                model.IsUsing,
                model.IsNew
            );
        }

        EncyclopediaEmblemListCellViewModel TranslateToEmblemListCellViewModel(EncyclopediaEmblemListCellModel model)
        {
            return new EncyclopediaEmblemListCellViewModel(
                model.MstEmblemId,
                model.AssetPath,
                model.IsUnlocked,
                model.IsNew
            );
        }

        NotificationBadge CreateNotificationBadge(
            IReadOnlyList<MasterDataId> displayedIds,
            MasterDataId targetId)
        {
            var badge = displayedIds.Contains(targetId);
            return new NotificationBadge(badge);
        }
    }
}
