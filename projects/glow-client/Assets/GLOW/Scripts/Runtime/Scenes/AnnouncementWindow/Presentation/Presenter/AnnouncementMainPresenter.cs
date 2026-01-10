using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.AnnouncementWindow.Domain.UseCase;
using GLOW.Scenes.AnnouncementWindow.Presentation.Translator;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Presenter
{
    /// <summary>
    /// 121_メニュー
    /// 　121-3_お知らせ
    /// 　　121-3-1_お知らせ
    /// </summary>
    public class AnnouncementMainPresenter : IAnnouncementMainViewDelegate
    {
        [Inject] AnnouncementMainViewController ViewController { get; }
        [Inject] AnnouncementMainViewController.Argument Argument { get; }
        [Inject] UpdateAnnouncementListUseCase UpdateAnnouncementListUseCase { get; }
        [Inject] SaveAnnouncementReadTimeUseCase SaveAnnouncementReadTimeUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetCachedAnnouncementListUseCase GetCachedAnnouncementListUseCase { get; }

        AnnouncementMainViewModel _viewModel;

        AnnouncementTabType _currentTabType = AnnouncementTabType.Event;

        CancellationToken AnnouncementCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        public void OnViewDidLoad()
        {
            // 0. ボタンを押しても反応しないようにする
            // 1. お知らせの情報を取得する
            // 2. 1の情報を元にお知らせを表示する上で必要なバナーUIを生成する
            // 3, それをContentsに入れる
            // 4. 3に対してデータを設定する
            ViewController.SetButtonInteractable(false);

            DoAsync.Invoke(AnnouncementCancellationToken, async cancellationToken =>
            {
                var model = await UpdateAnnouncementListUseCase.UpdateAndGetAnnouncementList(
                    cancellationToken,
                    Argument.DisplayMeansType);
                _viewModel = AnnouncementMainViewModelTranslator.ToAnnouncementMainViewModel(model);
                ViewController.SetTabBadge(_viewModel);
                ViewController.SetButtonInteractable(true);
                ViewController.ShowCurrentContent(CreateCurrentContent(), _currentTabType);
            });
        }

        public void OnEventTabSelected()
        {
            if(_currentTabType == AnnouncementTabType.Event) return;

            RemoveCurrentContent();
            var updatedList = GetCachedAnnouncementListUseCase.GetCacheInformationList(
                AnnouncementTabType.Event,
                ViewController.ReadInformationAnnouncementIds);
            _viewModel = AnnouncementMainViewModelTranslator.ToAnnouncementMainViewModel(updatedList);
            _currentTabType = AnnouncementTabType.Event;
            ViewController.SetTabBadge(_viewModel);
            ViewController.ShowCurrentContent(CreateCurrentContent(), _currentTabType);
        }

        public void OnOperationTabSelected()
        {
            if(_currentTabType == AnnouncementTabType.Operation) return;

            RemoveCurrentContent();
            var updatedList = GetCachedAnnouncementListUseCase.GetCacheInformationList(
                AnnouncementTabType.Operation,
                ViewController.ReadOperationAnnouncementIds);
            _viewModel = AnnouncementMainViewModelTranslator.ToAnnouncementMainViewModel(updatedList);
            _currentTabType = AnnouncementTabType.Operation;
            ViewController.SetTabBadge(_viewModel);
            ViewController.ShowCurrentContent(CreateCurrentContent(), _currentTabType);
        }

        public void OnCloseSelected()
        {
            if (!ViewController.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            ViewController.SetInteractable(false);

            var alreadyReadAnnouncement = SaveAnnouncementReadTimeUseCase.SaveReadAnnouncement(
                ViewController.ReadInformationAnnouncementIds,
                ViewController.ReadOperationAnnouncementIds);

            ViewController.OnCloseCompletion?.Invoke(alreadyReadAnnouncement);
            ViewController.Dismiss();
        }

        AnnouncementContentViewController CreateCurrentContent()
        {
            AnnouncementContentViewController contentViewController;
            if(_currentTabType == AnnouncementTabType.Event)
            {
                var argument =
                    new AnnouncementContentViewController.Argument(
                        _viewModel.AnnouncementEventViewModel.InformationEventCellViewModels, _viewModel.HookedPatternUrlInAnnouncements);
                contentViewController =  ViewFactory
                    .Create<AnnouncementContentViewController, AnnouncementContentViewController.Argument>(argument);
            }
            else
            {
                var argument = new AnnouncementContentViewController.Argument(
                    _viewModel.AnnouncementOperationViewModel.InformationOperationCellViewModels, _viewModel.HookedPatternUrlInAnnouncements);
                contentViewController = ViewFactory
                    .Create<AnnouncementContentViewController, AnnouncementContentViewController.Argument>(argument);
            }
            contentViewController.OnReadAnnouncement = OnReadAnnouncement;

            return contentViewController;
        }

        void RemoveCurrentContent()
        {
            ViewController.CurrentContentViewController?.Dismiss();
        }

        void OnReadAnnouncement(AnnouncementId announcementId)
        {
            if (_currentTabType == AnnouncementTabType.Event)
            {
                ViewController.ReadInformationAnnouncementIds.Add(announcementId);
            }
            else
            {
                ViewController.ReadOperationAnnouncementIds.Add(announcementId);
            }

            ViewController.SetTabBadge(_viewModel);
        }
    }
}
