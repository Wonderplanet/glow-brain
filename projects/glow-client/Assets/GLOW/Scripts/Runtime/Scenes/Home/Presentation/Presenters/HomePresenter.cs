using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.Misc;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.UserProfile.Presentation.Views;
using GLOW.Scenes.ItemBox.Presentation.Navigation;
using GLOW.Scenes.PvpTop.Domain;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect;
using GLOW.Scenes.UnitTab.Domain.UseCase;
using GLOW.Scenes.UnitTab.Presentation.Views;
using GLOW.Scenes.UserEmblem.Presentation.Views;
using GLOW.Scenes.UserLevelUp.Domain.UseCase;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using GLOW.Scenes.UserLevelUp.Presentation.Translator;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Exceptions;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;
using NotificationBadge = GLOW.Core.Domain.ValueObjects.NotificationBadge;

#if GLOW_DEBUG
using GLOW.Debugs.Command.Presentations;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Home.Presentation.DebugCommands;
#endif //DEBUG

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public sealed class HomePresenter : IHomeViewDelegate,
        IHomeHeaderDelegate,
        IHomeFooterDelegate,
        IPartyFormationApplier,
        IOutpostArtworkApplier,
        IGachaTransitionNavigator,
        IHomeViewControl,
        ITutorialHomeViewDelegate
    {
        [Inject] HomeViewController ViewController { get; }
        [Inject] IHomeTapBlock HomeTapBlock { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IHomeUseCases UseCases { get; }
        [Inject] GetStaminaUseCase GetStaminaUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] InitializeNewShopProductIdUseCase InitializeNewShopProductIdUseCase { get; }
        [Inject] GetGachaNoticeUseCase GetGachaNoticeUseCase { get; }
        [Inject] GetShopProductNoticeUseCase GetShopProductNoticeUseCase { get; }
        [Inject] GetPackProductNoticeUseCase GetPackProductNoticeUseCase { get; }
        [Inject] GetPassProductNoticeUseCase GetPassProductNoticeUseCase { get; }
        [Inject] GetUnitNoticeUseCase GetUnitNoticeUseCase { get; }
        [Inject] GetOutpostNoticeUseCase GetOutpostNoticeUseCase { get; }
        [Inject] GetEncyclopediaNoticeUseCase GetEncyclopediaNoticeUseCase { get; }
        [Inject] GetContentNoticeUseCase GetContentNoticeUseCase { get; }
        [Inject] HomeHeaderBadgeUseCase HomeHeaderBadgeUseCase { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ApplyPartyFormationUseCase ApplyPartyFormationUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ApplyOutpostArtworkUseCase ApplyOutpostArtworkUseCase { get; }
        [Inject] HomeHeaderIconUseCase HomeHeaderIconUseCase { get; }
        [Inject] UpdatePartyMemberSlotUseCase UpdatePartyMemberSlotUseCase { get; }
        [Inject] SceneWireFrame SceneWireFrame { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }
        [Inject] CheckPvpOpenUseCase CheckPvpOpenUseCase { get; }
        [Inject] CheckOpenExchangeContentUseCase CheckOpenExchangeContentUseCase { get; }
        [Inject] IHomeBackgroundControl HomeBackgroundControl { get; }
        [Inject] HomeFooterBalloonUseCase HomeFooterBalloonUseCase { get; }
        [Inject] ShowUserLevelUpInfoUseCase ShowUserLevelUpInfoUseCase { get; }
        [Inject] IUserLevelUpResultViewFacade UserLevelUpResultViewFacade { get; }
        [Inject] IOutGameAssetUnLoader OutGameAssetUnLoader { get; }

        [Inject] IContentMaintenanceCoordinator ContentMaintenanceCoordinator { get; }
        [Inject] IContentMaintenanceHandler ContentMaintenanceHandler { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] ITutorialPlayingStatus TutorialPlayingStatus { get; }
        [Inject] GetUserMaxStaminaUseCase GetUserMaxStaminaUseCase { get; }
#if GLOW_DEBUG
        [Inject] HomeDebugCommandHandler HomeDebugCommandHandler { get; }
#endif

        HomeViewModelTranslator _translator = new();

        CancellationToken CancellationToken => ViewController.View.GetCancellationTokenOnDestroy();
        CancellationTokenSource _levelUpAnimationCancellationTokenSource = new();

        void IHomeViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(HomePresenter), nameof(IHomeViewDelegate.OnViewDidLoad));
            ContentMaintenanceCoordinator.SetUp(ContentMaintenanceHandler);

            // NOTE: 初期表示のコンテンツを設定する
            // NOTE: フッターを設定する
            InitViewControllers();

            // NOTE: ヘッダーにユーザー情報を設定する
            UpdateHeaderUserProfile();

            // NOTE: ヘッダーの状態を監視する
            MonitorHeaderStatus();
            //スタミナ回復広告バッジの確認(これ以降はMonitorHeaderStatus内で定期的に確認)
            HomeHeaderDelegate.UpdateBadgeStatus();

            // NOTE : 日が変わっていた場合はショップのNEW状態を初期化する
            InitializeNewShopProductIdUseCase.InitializeNewShopProductId();

            // NOTE: パーティキャッシュ情報にキャラ枠数の設定を行う
            UpdatePartyMemberSlotUseCase.UpdatePartyMemberSlot();

            SetFooterStatus();

#if GLOW_DEBUG
            HomeDebugCommandHandler.OnBackTitle = OnBackTitle;

            DebugCommandActivator.OnDebugCommandActivated += DebugCommandActivated;
#endif
        }

        void IHomeViewDelegate.OnViewWillAppear()
        {
            HomeBackgroundControl.ShowBasicBackground(BasicHomeBackgroundType.Default);
        }

        void IHomeViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(HomePresenter), nameof(IHomeViewDelegate.OnViewDidUnload));
#if GLOW_DEBUG
            DebugCommandActivator.OnDebugCommandActivated -= DebugCommandActivated;
#endif
            OutGameAssetUnLoader.UnLoad();
        }

        bool ITutorialHomeViewDelegate.IsPresented<T>()
        {
            return ViewController.TutorialViewChangeMonitor.CurrentViewController is T;
        }

        void ITutorialHomeViewDelegate.EnableHomeHeaderTap()
        {
            ViewController.EnableHomeHeaderTap();
        }

        void ITutorialHomeViewDelegate.DisableHomeHeaderTap()
        {
            ViewController.DisableHomeHeaderTap();
        }

        void IHomeViewDelegate.OnContentSelected(HomeContentTypes contentType)
        {
            if (ViewController.ViewContextController.CurrentContentType == contentType)
            {
                return;
            }
            DoAsync.Invoke(ViewController.View.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                // onCompleteで呼ばれるコールバックはSwitchContentAsyncの後に呼ぶ処理とタイミングが違うので注意
                await SwitchContentAsync(contentType, () => PostProcessContentSelected(contentType));
            });
        }

        void PostProcessContentSelected(HomeContentTypes contentType)
        {
            // Characterだったらデフォルトタブに変更する
            if (contentType == HomeContentTypes.Character)
            {
                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.UnitTabViewController.ViewController as IUnitTabViewControl;
                control?.TransitToUnitList();
            }
        }

        void IHomeViewDelegate.OnDiamondSelected()
        {
            OnBasicShopSelected();
        }

        void IHomeViewDelegate.OnAvatarSelected()
        {
            var controller = ViewFactory.Create<UserProfileViewController>();
            ViewController.PresentModally(controller);
        }

        void IHomeViewDelegate.OnEmblemSelected()
        {
            var controller = ViewFactory.Create<UserEmblemViewController>();
            ViewController.PresentModally(controller);
        }

        void IHomeViewDelegate.OnProfileSelected()
        {
            NotImpl.Handle();
        }

        void IHomeViewDelegate.OnGachaButtonTapped()
        {
            // ガシャ(全体)部分メンテ時は遷移しない、ただしメインチュートリアルは例外とする
            if (!TutorialPlayingStatus.IsPlayingTutorialSequence
                && CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha()))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            SwitchContentAsync(HomeContentTypes.Gacha).Forget();
        }

        void IHomeViewDelegate.OnShopButtonTapped()
        {
            // 部分メンテ、ショップ全体がメンテ中の場合は遷移しない
            if (CheckContentMaintenanceUseCase.IsInMaintenance( ContentMaintenanceTarget.Shop))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            var viewContextController = ViewController.ViewContextController;
            var vc = viewContextController.ShopTabViewController.ViewController;
            vc.IsTransitionedByFooter = true;
            SwitchContentAsync(HomeContentTypes.Shop).Forget();
        }

        void IHomeViewControl.OnIdleIncentiveTopSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnIdleIncentiveTopSelected();
            });
        }

        void IHomeViewControl.OnQuestSelected()
        {
            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);
                await UniTask.WaitUntil(
                    () => !HomeViewNavigation.HasRunningViewNavigationCoroutine(),
                    cancellationToken: cancellationToken);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnQuestSelected();
            });
        }

        void IHomeViewControl.OnUnitListSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Character);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.UnitTabViewController.ViewController as IUnitTabViewControl;
                control?.TransitToUnitList();
            });
        }

        void IHomeViewControl.OnQuestSelectedFromHome(MasterDataId masterDataId, bool popBeforeDetail)
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                // DetailViewを作る際にTryPopをするか選択
                if (popBeforeDetail)
                {
                    HomeViewNavigation.TryPop(false, () =>
                    {
                        var viewContextController = ViewController.ViewContextController;
                        var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                        control?.OnQuestSelectedWithId(masterDataId);
                    });

                    return;
                }

                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnQuestSelectedWithId(masterDataId);
            });
        }

        void IHomeViewControl.OnNormalMissionSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnMissionSelected();
            });
        }

        void IHomeViewControl.OnNormalMissionSelectedFromHome(MissionType missionType, bool isDisplayFromItemDetail)
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnMissionSelectedForType(missionType, isDisplayFromItemDetail);
            });
        }

        void IHomeViewControl.OnOutpostEnhanceSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Character);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.UnitTabViewController.ViewController as IUnitTabViewControl;
                control?.TransitToOutpostEnhance();
            });
        }

        void IHomeViewControl.OnPartyFormationSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Character);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.UnitTabViewController.ViewController as IUnitTabViewControl;
                control?.TransitToPartyFormation();
            });
        }

        void IHomeViewControl.OnPackShopSelected()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopPack))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            if (!ShouldSwitchContent(HomeContentTypes.Shop)) return;

            // 表示するタブに切り替えてから遷移する
            var viewContextController = ViewController.ViewContextController;
            var vc = viewContextController.ShopTabViewController.ViewController;
            vc?.OnChangeShopContent(ShopContentTypes.Pack, MasterDataId.Empty);

            SwitchContentAsync(HomeContentTypes.Shop).Forget();
        }

        void IHomeViewControl.OnPassShopSelected()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopPass))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            if (!ShouldSwitchContent(HomeContentTypes.Shop)) return;

            // 表示するタブに切り替えてから遷移する
            var viewContextController = ViewController.ViewContextController;
            var vc = viewContextController.ShopTabViewController.ViewController;
            vc?.OnChangeShopContent(ShopContentTypes.Pass, MasterDataId.Empty);

            SwitchContentAsync(HomeContentTypes.Shop).Forget();
        }

        void IHomeViewControl.OnLinkBnIdSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnBnIdLinkSelected();
            });
        }

        void IHomeViewControl.OnBasicShopSelected()
        {
            OnBasicShopSelected();
        }

        void OnBasicShopSelected()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopItem))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            if (!ShouldSwitchContent(HomeContentTypes.Shop)) return;
            // 表示するタブに切り替えてから遷移する
            var viewContextController = ViewController.ViewContextController;
            var vc = viewContextController.ShopTabViewController.ViewController;
            vc?.OnChangeShopContent(ShopContentTypes.Shop, MasterDataId.Empty);

            SwitchContentAsync(HomeContentTypes.Shop).Forget();
        }

        void IHomeViewControl.OnPvpTopSelected()
        {
            var pvpOpeningStatusModel = CheckPvpOpenUseCase.GetModel();
            if (pvpOpeningStatusModel.OpeningStatusAtTimeType !=
                QuestContentOpeningStatusAtTimeType.Opening)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在、ランクマッチは\n開催されておりません。");
                return;
            }

            if (pvpOpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.RankLocked ||
                pvpOpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.StageLocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    pvpOpeningStatusModel.QuestContentReleaseRequiredSentence.Value);
                return;
            }

            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Pvp))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            var firstViewController = ViewController.ViewContextController.QuestContentTopViewController;
            var controllers = new List<(UIViewController, HomeContentDisplayType)>
            {
                (firstViewController.ViewController, firstViewController.HomeContentDisplayType),
                (ViewFactory.Create<PvpTopViewController>(), HomeContentDisplayType.BottomOverlap),
            };
            SwitchContentAsync(HomeContentTypes.Content, controllers).Forget();
        }

        void IHomeViewControl.OnShopSelectedFromHome(ShopContentTypes shopContentType, MasterDataId masterDataId)
        {
            var contentMaintenanceType = shopContentType switch
            {
                ShopContentTypes.Shop => ContentMaintenanceType.ShopItem,
                ShopContentTypes.Pack => ContentMaintenanceType.ShopPack,
                ShopContentTypes.Pass => ContentMaintenanceType.ShopPass,
                _ => ContentMaintenanceType.Non,
            };
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopTab(contentMaintenanceType)))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            if (!ShouldSwitchContent(HomeContentTypes.Shop)) return;

            // 表示するタブに切り替えてから遷移する
            var vc = ViewController.ViewContextController
                .ShopTabViewController.ViewController;
            vc?.OnChangeShopContent(shopContentType, masterDataId);
            SwitchContentAsync(HomeContentTypes.Shop).Forget();
        }

        void IHomeViewControl.OnGachaSelected()
        {
            ShowGacha();
        }

        void IHomeViewControl.OnGachaContentSelectedFromHome(MasterDataId gachaId)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha(gachaId)))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            if (!ShouldSwitchContent(HomeContentTypes.Gacha)) return;

            var firstViewController = ViewController.ViewContextController.GachaListViewController;
            var factory = (IGachaContentViewFactory)firstViewController.ViewController;

            var gachaContentVc = factory.CreateGachaContentViewController(gachaId);
            var controllers = new List<(UIViewController, HomeContentDisplayType)>
            {
                (firstViewController.ViewController, HomeContentDisplayType.Default),
                (gachaContentVc, HomeContentDisplayType.BottomOverlap)
            };
            SwitchContentAsync(HomeContentTypes.Gacha, controllers).Forget();
        }

        void IHomeViewControl.OnExchangeContentTopSelected()
        {
            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnExchangeContentTopSelected();
            });
        }

        void IHomeViewControl.OnExchangeShopTopSelected(MasterDataId mstExchangeId)
        {
            if (!CheckOpenExchangeContentUseCase.CheckOpenExchangeContent(mstExchangeId))
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在、交換所は\n開催されておりません。");
                return;
            }

            DoAsync.Invoke(CancellationToken, async _ =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);

                var viewContextController = ViewController.ViewContextController;
                var control = viewContextController.HomeMainViewController.ViewController as IHomeMainViewControl;
                control?.OnExchangeShopTopSelected(mstExchangeId);
            });
        }

        void IHomeViewControl.OnContentTopSelected()
        {
            if (!ShouldSwitchContent(HomeContentTypes.Content))
            {
                HomeViewNavigation.TryPopToRoot();
                return;
            }
            SwitchContentAsync(HomeContentTypes.Content).Forget();
        }

        void IHomeViewControl.OnEventQuestSelectedFromHome(MasterDataId mstEventId)
        {
            // 遷移先の指定がなければコンテンツTOPに遷移
            if (mstEventId.IsEmpty() || !EventOpenCheckUseCase.IsOpenEvent(mstEventId))
            {
                SwitchContentAsync(HomeContentTypes.Content).Forget();
                return;
            }

            var firstViewController = ViewController.ViewContextController.QuestContentTopViewController;

            var secondViewController =
                ViewFactory.Create<EventQuestSelectViewController, EventQuestSelectViewController.Argument>(
                    new EventQuestSelectViewController.Argument(mstEventId));

            var controllers = new List<(UIViewController, HomeContentDisplayType)>
            {
                (firstViewController.ViewController, firstViewController.HomeContentDisplayType),
                (secondViewController, HomeContentDisplayType.BottomOverlap),
            };
            SwitchContentAsync(HomeContentTypes.Content, controllers).Forget();
        }

        void IHomeViewDelegate.ShowTapBlock(bool shouldShowGrayScale, RectTransform invertMaskRect, float duration)
        {
            HomeTapBlock.ShowTapBlock(shouldShowGrayScale, invertMaskRect, duration);
        }

        void IHomeViewDelegate.HideTapBlock(bool shouldShowGrayScale, float duration)
        {
            HomeTapBlock.HideTapBlock(shouldShowGrayScale, duration);
        }

        void IHomeViewDelegate.OnBackTitle()
        {
            OnBackTitle();
        }

        void IHomeViewDelegate.SetBackKeyEnabled(bool isEnabled)
        {
            ViewController.SetBackKeyEnabled(isEnabled);
        }

        void OnBackTitle()
        {
            // NOTE: アプリを終了するかタイトルへ戻るかを選択する
            MessageViewUtil.ShowMessageWith2Buttons(
                "タイトル移動確認",
                "タイトル画面に戻りますか？",
                "",
                "はい",
                "キャンセル",
                () => ApplicationRebootor.Reboot(),
                () => { },
                () => { });
        }

        void IHomeHeaderDelegate.UpdateStatus()
        {
            UpdateHeaderUserProfile();
        }

        void IHomeHeaderDelegate.UpdateBadgeStatus()
        {
            var model = HomeHeaderBadgeUseCase.GetHeaderBadgeModel();
            ViewController.SetHeaderBadge(model);
        }

        void IHomeHeaderDelegate.OnStaminaRecoverButton()
        {
            var userParameterModel = UseCases.GetUserParameter();
            var maxStamina = GetUserMaxStaminaUseCase.GetUserMaxStamina();
            if (maxStamina.Value <= userParameterModel.Stamina.Value)
            {
                var message = $"スタミナの所持上限は{maxStamina.Value}となっているため、これ以上回復できません。";
                MessageViewUtil.ShowMessageWithOk(
                    "確認",
                    message);
                return;
            }

            var argument = new StaminaRecoverySelectViewController.Argument(StaminaShortageFlag.False);
            var controller = ViewFactory.Create<StaminaRecoverySelectViewController,
                StaminaRecoverySelectViewController.Argument>(argument);
            
            ViewController.PresentModally(controller);
        }

        void IHomeHeaderDelegate.PlayExpGaugeAnimation()
        {
            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                ViewController.ActualView.UserInteraction = false;
                var userLevelUpInfo = GetUserLevelUpInfo();
                await PlayExpGaugeAnimation(cancellationToken, userLevelUpInfo);
                UserLevelUpResultViewFacade.Show(userLevelUpInfo.UserLevelUpEffectModel);
                ViewController.ActualView.UserInteraction = true;
            });
        }

        async UniTask IHomeHeaderDelegate.PlayExpGaugeAnimationAsync(CancellationToken cancellationToken)
        {
            _levelUpAnimationCancellationTokenSource?.Cancel();
            _levelUpAnimationCancellationTokenSource?.Dispose();

            // 外から呼ばれる場合は、外部のCancellationTokenとHome側のCancellationTokenを紐づける
            _levelUpAnimationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken,
                CancellationToken);

            var userLevelUpInfo = GetUserLevelUpInfo();

            try
            {
                ViewController.ActualView.UserInteraction = false;
                await PlayExpGaugeAnimation(_levelUpAnimationCancellationTokenSource.Token, userLevelUpInfo);
                UserLevelUpResultViewFacade.Show(userLevelUpInfo.UserLevelUpEffectModel);
            }
            finally
            {
                // 非同期処理が中断された場合は必ずDisposeする
                _levelUpAnimationCancellationTokenSource?.Dispose();
                _levelUpAnimationCancellationTokenSource = null;

                // 経験値反映後の状態にする
                if (!userLevelUpInfo.IsEmpty())
                {
                    ViewController.SetExpGauge(
                        userLevelUpInfo.CurrentExp,
                        userLevelUpInfo.NextLevelExp);
                }

                if (userLevelUpInfo.IsLevelUp())
                {
                    ViewController.SetLevel(userLevelUpInfo.UserLevelUpEffectModel.NextUserLevel);
                }

                // タップガードを解除
                ViewController.ActualView.UserInteraction = true;
            }
        }

        void IHomeFooterDelegate.UpdateBadgeStatus()
        {
            SetFooterStatus();
        }

        void IHomeFooterDelegate.BackToHome()
        {
            DoAsync.Invoke(ViewController.View.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await SwitchContentAsync(HomeContentTypes.Main);
            });
        }

        void IHomeFooterDelegate.UpdateFooterBalloon()
        {
            var shouldShowTopView = ViewController.TopViewController is HomeMainViewController;
            if (!shouldShowTopView)
            {
                ViewController.SetFooterBalloon(HomeFooterBalloonUseCaseModel.Empty);
                return;
            }

            var model = HomeFooterBalloonUseCase.GetHomeFooterBalloonUseCaseModel();
            ViewController.SetFooterBalloon(model);
        }

        void InitViewControllers()
        {
            var viewModel = SceneWireFrame.GetInitialViewControllers();
            ViewController.Init(viewModel.VCs, viewModel.HomeContentType);
            InitBGM(viewModel.HomeContentType);
        }

        void InitBGM(HomeContentTypes type)
        {
            var bgmAssetKey = type switch
            {
                HomeContentTypes.Content => BGMAssetKeyDefinitions.BGM_quest_content_top,
                _ => BGMAssetKeyDefinitions.BGM_home
            };
            BackgroundMusicPlayable.Play(bgmAssetKey);
        }

        bool ShouldSwitchContent(HomeContentTypes targetType)
        {
            return ViewController.ViewContextController.CurrentContentType != targetType;
        }

        // onCompleteで呼ばれるコールバックはSwitchContentAsyncの後に呼ぶ処理とタイミングが違うので注意
        async UniTask SwitchContentAsync(HomeContentTypes contentType, Action onComplete = null)
        {
            if (contentType == ViewController.ViewContextController.CurrentContentType)
            {
                return;
            }
            // NOTE: 切り替え時のトランジションなど
            ViewController.View.UserInteraction = false;
            await UniTask.Yield(CancellationToken);
            var completion = false;
            HomeViewNavigation.Switch(contentType,
                completion:() =>
                {
                    completion = true;
                    onComplete?.Invoke();
                });
            SetFooterStatus();
            // NOTE: 切り替え時のトランジションなど
            await UniTask.WaitUntil(() => completion, cancellationToken: CancellationToken);
            ViewController.View.UserInteraction = true;
        }

        // コンテキスト最初の画面(controllers.First)はHomeViewController.ContextViewControllerから取得する必要あり
        async UniTask SwitchContentAsync(
            HomeContentTypes contentType,
            IReadOnlyList<(UIViewController controllers, HomeContentDisplayType type)> controllers)
        {
            if (!controllers.Any()) return;

            // NOTE: 切り替え時のトランジションなど
            ViewController.View.UserInteraction = false;
            await UniTask.Yield(CancellationToken);

            HomeViewNavigation.SwitchMultipleViewController(controllers, contentType);

            // NOTE: 切り替え時のトランジションなど
            await UniTask.Yield(CancellationToken);

            ViewController.View.UserInteraction = true;
        }

        void IPartyFormationApplier.ApplyPartyFormation()
        {
            var needsApplyParty = ApplyPartyFormationUseCase.GetNeedApplyPartyFormation();
            if (null == needsApplyParty || needsApplyParty.IsEmpty()) return;

            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                await ApplyPartyFormationUseCase.ApplyPartyFormation(cancellationToken, needsApplyParty);
            });
        }

        public async UniTask ApplyOutpostArtwork(CancellationToken cancellationToken)
        {
            var isNeedApply = ApplyOutpostArtworkUseCase.IsNeedApplyOutpostArtwork();
            if (!isNeedApply) return;

            await ApplyOutpostArtworkUseCase.ChangeArtwork(cancellationToken);
        }

        void IOutpostArtworkApplier.AsyncApplyOutpostArtwork()
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                ApplyOutpostArtwork(cancellationToken);
            });
        }

        void IGachaTransitionNavigator.ShowGachaListView()
        {
            ShowGacha();
        }

        void ShowGacha()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.Gacha()))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }
            if (!ShouldSwitchContent(HomeContentTypes.Gacha)) return;
            SwitchContentAsync(HomeContentTypes.Gacha).Forget();
        }

        void UpdateHeaderUserProfile()
        {
            var userParameterModel = UseCases.GetUserParameter();
            var userProfileModel = UseCases.GetUserProfile();
            var userHeaderIconModel =
                HomeHeaderIconUseCase.GetHomeHeaderIcon(userProfileModel.MstUnitId, userProfileModel.MstEmblemId);

            var viewModel = _translator.GenerateHomeHeaderViewModel(
                userParameterModel,
                userProfileModel,
                userHeaderIconModel);
            var staminaViewModel = _translator.GenerateHomeHeaderStaminaViewModel(GetStaminaUseCase.GetUserStamina());
            ViewController.SetHeaderViewModel(viewModel, staminaViewModel); //ここ先発、数値入ってくる
        }

        void MonitorHeaderStatus()
        {
            //ヘッダーのスタミナ・スタミナ購入広告バッジの処理を行う
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                // NOTE: 毎ループ確認する
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    // NOTE: CancellationTokenがキャンセルされたら処理を終了する
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    // NOTE: １秒間待機（FPSレベルで更新しない）
                    await UniTask.Delay(TimeSpan.FromSeconds(1.0), cancellationToken: cancellationToken);

                    //スタミナ数値更新
                    var staminaModel = GetStaminaUseCase.GetUserStamina();
                    ViewController.SetHeaderStaminaViewModel(_translator.GenerateHomeHeaderStaminaViewModel(staminaModel));

                    //スタミナ回復広告バッジの確認
                    HomeHeaderDelegate.UpdateBadgeStatus();

                    //フッターのバッジの確認(もし日跨ぎとかで更新監視が必要ならコメント外す)
                    // SetFooterStatus();
                }
            });
        }

        void SetFooterStatus()
        {
            bool isGachaNotice = GetGachaNoticeUseCase.GetGachaNotice();
            bool isNewShopProduct = GetShopProductNoticeUseCase.GetShopProductNotice(false);
            bool isNewPackProduct = GetPackProductNoticeUseCase.GetPackProductNotice();
            bool isNewPassProduct = GetPassProductNoticeUseCase.GetPassProductNotice();
            var isGradeUpUnit = GetUnitNoticeUseCase.GetUnitNotification();
            var isNewOutpostArtwork = GetOutpostNoticeUseCase.GetUnitNotification();
            var isUnReceivedEncyclopediaReward = GetEncyclopediaNoticeUseCase.GetEncyclopediaNotification();
            var isContentNotice = GetContentNoticeUseCase.GetContentNotification();

            //必要あればUseCaseから情報取得
            var viewModel = new HomeFooterViewModel(
                new NotificationBadge(isGachaNotice),
                new NotificationBadge(isGradeUpUnit.Value | isNewOutpostArtwork.Value),
                new NotificationBadge(false),
                isContentNotice,
                new NotificationBadge(isNewShopProduct || isNewPackProduct || isNewPassProduct));
            ViewController.SetFooterViewModel(viewModel);
        }

        async UniTask PlayExpGaugeAnimation(
            CancellationToken cancellationToken,
            UserLevelUpInfoViewModel userLevelUpInfoViewModel)
        {
            if (userLevelUpInfoViewModel.IsEmpty())
            {
                return;
            }

            await ViewController.PlayExpGaugeAnimation(
                cancellationToken,
                userLevelUpInfoViewModel.UserExpGainModels,
                userLevelUpInfoViewModel.CurrentExp,
                userLevelUpInfoViewModel.NextLevelExp);
        }

        UserLevelUpInfoViewModel GetUserLevelUpInfo()
        {
            var userLevelUpModel = ShowUserLevelUpInfoUseCase.GetUserLevelUpInfo();
            if (!userLevelUpModel.IsExpChange)
            {
                return UserLevelUpInfoViewModel.Empty;
            }

            var userLevelUpViewModel = UserLevelUpInfoViewModelTranslator.ToUserLevelUpInfoViewModel(userLevelUpModel);
            return userLevelUpViewModel;
        }

#if GLOW_DEBUG
        void DebugCommandActivated(IDebugCommandPresenter debugCommandPresenter)
        {
            ApplicationLog.Log(nameof(HomePresenter), nameof(DebugCommandActivated));
            debugCommandPresenter.CreateRootMenu = HomeDebugCommandHandler.CreateDebugCommandRootMenu;
        }
#endif
    }
}
