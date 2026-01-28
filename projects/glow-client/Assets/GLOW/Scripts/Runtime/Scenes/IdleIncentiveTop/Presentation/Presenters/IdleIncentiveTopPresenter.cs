using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;
using GLOW.Scenes.IdleIncentiveTop.Domain.UseCase;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.Shop.Domain.Calculator;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;
using AmountFormatter = GLOW.Core.Presentation.Modules.AmountFormatter;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Presenters
{
    public sealed class IdleIncentiveTopPresenter : IIdleIncentiveTopViewDelegate
    {
        [Inject] IdleIncentiveTopViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] GetIdleIncentiveTopModelUseCase TopModelUseCase { get; }
        [Inject] GetIdleIncentiveTopStageUseCase GetIdleIncentiveTopStageUseCase { get; }
        [Inject] GetIdleIncentiveRewardUseCase RewardUseCase { get; }
        [Inject] ReceiveIdleIncentiveRewardUseCase ReceiveRewardUseCase { get; }
        [Inject] GetIdleIncentiveElapsedTimeUseCase ElapsedTimeUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }

        Coroutine _elapsedTimeCoroutine;
        Coroutine _receiveIntervalTime;

        TimeSpan _maxIdleHour;
        TimeSpan _idleIntervalMinute;

        IdleIncentiveRewardListViewModel _rewardListViewModel;

        void IIdleIncentiveTopViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(IdleIncentiveTopPresenter), nameof(IIdleIncentiveTopViewDelegate.OnViewDidLoad));

            var model = TopModelUseCase.GetIdleIncentiveTopModel();
            _maxIdleHour = model.MaxIdleHour;
            _idleIntervalMinute = model.IdleIntervalMinute;
            var viewModel = Translate(model);
            ViewController.Setup(viewModel);
            _receiveIntervalTime = ViewController.View.StartCoroutine(AutoUpdateReceiveIntervalTime());

            var topStageModel = GetIdleIncentiveTopStageUseCase.GetTopStageModel();

            var characterViewModel = new IdleIncentiveTopCharacterViewModel(
                topStageModel.PlayerUnit.UnitImageAssetPath,
                topStageModel.EnemyUnit.UnitImageAssetPath,
                topStageModel.EnemyUnit.IsPhantomized,
                topStageModel.PlayerUnit.RoleType,
                topStageModel.PlayerUnit.AttackDelay,
                topStageModel.PlayerUnit.AttackRange,
                topStageModel.PlayerUnit.AssetKey,
                topStageModel.BackgroundAssetKey);

            ViewController.SetupBackground(characterViewModel.BackgroundAssetKey);

            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                await ViewController.SetAnimationCharacter(cancellationToken, characterViewModel);
            });
        }

        void IIdleIncentiveTopViewDelegate.OnViewDidAppear()
        {
            ApplicationLog.Log(nameof(IdleIncentiveTopPresenter), nameof(IIdleIncentiveTopViewDelegate.OnViewDidAppear));

            if (_elapsedTimeCoroutine == null)
            {
                _elapsedTimeCoroutine = ViewController.View.StartCoroutine(AutoUpdateElapsedTime());
            }
        }

        void IIdleIncentiveTopViewDelegate.OnBackSelected()
        {
            HomeViewNavigation.TryPop();
        }

        void IIdleIncentiveTopViewDelegate.OnReceiveSelected()
        {
            DoAsync.Invoke(ViewController.View.gameObject, async cancellationToken =>
            {
                IReadOnlyList<CommonReceiveResourceModel> models;
                using (ScreenInteractionControl.Lock())
                {
                    models = await ReceiveRewardUseCase.ReceiveIdleIncentiveReward(cancellationToken);
                    // ローカル通知のスケジュールを更新
                    LocalNotificationScheduler.RefreshIdleIncentiveSchedule();

                    HomeHeaderDelegate.UpdateStatus();
                }

                var viewModels =
                    models
                        .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();

                await CommonReceiveWireFrame.ShowAsync(cancellationToken,
                    viewModels,
                    RewardTitle.Default,
                    ReceivedRewardDescription.Empty);

                await HomeHeaderDelegate.PlayExpGaugeAnimationAsync(cancellationToken);

                RestartAutoUpdate();
            });
        }

        void IIdleIncentiveTopViewDelegate.OnQuickReceiveSelected()
        {
            DoAsync.Invoke(ViewController.View.gameObject, ViewController, async ct =>
            {
                var viewController = ViewFactory.Create<IdleIncentiveQuickReceiveWindowViewController>();
                ViewController.PresentModally(viewController);
                await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: ct);
                var model = TopModelUseCase.GetIdleIncentiveTopModel();
                var viewModel = Translate(model);
                ViewController.Setup(viewModel);
            });
        }

        void RestartAutoUpdate()
        {
            if (null != _elapsedTimeCoroutine)
            {
                ViewController.View.StopCoroutine(_elapsedTimeCoroutine);
            }

            if (null != _receiveIntervalTime)
            {
                ViewController.View.StopCoroutine(_receiveIntervalTime);
            }

            _elapsedTimeCoroutine = ViewController.View.StartCoroutine(AutoUpdateElapsedTime());
            _receiveIntervalTime = ViewController.View.StartCoroutine(AutoUpdateReceiveIntervalTime());
        }

        IdleIncentiveTopViewModel Translate(IdleIncentiveTopModel topModel)
        {
            return new IdleIncentiveTopViewModel(
                topModel.OneHourCoinReward,
                topModel.PassEffectCoinReward,
                topModel.OneHourExpReward,
                topModel.PassEffectExpReward,
                topModel.EnableQuickReward,
                ZString.Format("{0}時間", topModel.MaxIdleHour.TotalHours),
                topModel.PassEffectDisplayModels.Select(
                        HeldPassEffectDisplayViewModelTranslator.ToHeldPassEffectDisplayViewModel)
                    .ToList());
        }

        IEnumerator AutoUpdateElapsedTime()
        {
            var elapsedTime = ElapsedTimeUseCase.GetIdleIncentiveElapsedTimeSpan();
            double nextUpdateRewardTotalMinutes = 0;
            var waitTime = elapsedTime.Milliseconds * 0.001f;
            while (elapsedTime < _maxIdleHour)
            {
                ViewController.UpdateElapsedTime(
                    ZString.Format("{0}:{1:mm\\:ss}", Math.Floor(elapsedTime.TotalHours), elapsedTime),
                    false);

                if (elapsedTime.TotalMinutes > nextUpdateRewardTotalMinutes)
                {
                    UpdateRewardList(elapsedTime);

                    nextUpdateRewardTotalMinutes =
                        (Math.Floor(elapsedTime.TotalMinutes / (int)_idleIntervalMinute.TotalMinutes) + 1)
                        * _idleIntervalMinute.TotalMinutes;
                }

                yield return new WaitForSeconds(waitTime);
                elapsedTime = ElapsedTimeUseCase.GetIdleIncentiveElapsedTimeSpan();
                waitTime = 1;
            }

            ViewController.UpdateElapsedTime(
                ZString.Format("{0}:{1:mm\\:ss}", Math.Floor(elapsedTime.TotalHours), elapsedTime),
                true);

            UpdateRewardList(elapsedTime);
        }

        IEnumerator AutoUpdateReceiveIntervalTime()
        {
            var elapsedTime = ElapsedTimeUseCase.GetIdleIncentiveElapsedTimeSpan();
            var waitTime = elapsedTime.Milliseconds * 0.001f;
            while (elapsedTime < _idleIntervalMinute)
            {
                var interval = _idleIntervalMinute - elapsedTime + TimeSpan.FromSeconds(1); // 探索時間と１秒ずれるので表示上合わせる
                ViewController.UpdateReceiveInterval(AmountFormatter.FormatSecond(interval));
                yield return new WaitForSeconds(waitTime);
                elapsedTime = ElapsedTimeUseCase.GetIdleIncentiveElapsedTimeSpan();
                waitTime = 1;
            }

            ViewController.UpdateReceiveInterval(null);
        }

        void UpdateRewardList(TimeSpan currentElaspedTime)
        {
            var model = RewardUseCase.GetRewardList(currentElaspedTime);
            var viewModel = IdleIncentiveRewardListViewModelTranslator.TranslateRewardList(model);
            _rewardListViewModel = viewModel;
            ViewController.UpdateRewardList(viewModel);
            ViewController.PlayRewardListCellAppearanceAnimation();
        }
    }
}
