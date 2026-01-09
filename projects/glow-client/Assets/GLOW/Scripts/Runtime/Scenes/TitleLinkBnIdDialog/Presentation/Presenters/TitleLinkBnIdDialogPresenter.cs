using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models;
using GLOW.Scenes.TitleLinkBnIdDialog.Domain.UseCases;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Domain.UseCases;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.ViewModels;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Presenters
{
    public class TitleLinkBnIdDialogPresenter : ITitleLinkBnIdDialogViewDelegate
    {
        [Inject] TitleLinkBnIdDialogViewController ViewController { get; }
        [Inject] LinkBnIdConfirmUseCase LinkBnIdConfirmUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] LinkBnIdForTitleUseCase LinkBnIdForTitleUseCase { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        void ITitleLinkBnIdDialogViewDelegate.OnViewDidLoad()
        {
        }

        void ITitleLinkBnIdDialogViewDelegate.OnLinkBnId()
        {
            var controller = ViewFactory.Create<LinkBnIdWebViewDialogViewController>();
            controller.OnRedirected = ConfirmLinkBnId;
            ViewController.PresentModally(controller);
        }

        void ConfirmLinkBnId(BnIdCode bnIdCode)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    var model = await LinkBnIdConfirmUseCase.LinkBnIdConfirm(cancellationToken, bnIdCode);
                    var viewModel = CreateResultViewModel(model);

                    var argument = new TitleBnIdLinkageResultDialogViewController.Argument(viewModel);
                    var controller = ViewFactory.Create<TitleBnIdLinkageResultDialogViewController, TitleBnIdLinkageResultDialogViewController.Argument>(argument);

                    (var leftAction, var rightAction) = CreateResultButtonActions(model, controller);
                    controller.OnLeftButton = leftAction;
                    controller.OnRightButton = rightAction;

                    ViewController.PresentModally(controller);

                    ViewController.Dismiss();
                }
                catch (UserBnidAccessTokenApiErrorException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "連携情報取得失敗",
                        "連携情報の取得に失敗しました。\n\n再度お試しいただくか、お手数ですがお問い合わせください。");
                }
                catch (UserBnidLinkLimitException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "アカウント連携凍結中",
                        "入力されたバンダイナムコIDに\n連携されたゲームデータは、\n不正が疑われる操作を確認したため\nアカウント連携機能を凍結中です。\n\nゲームデータを共有できませんでした。");
                }
                catch (UserBnidLinkLimitMyAccountException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "アカウント連携停止中",
                        "ご利用中のゲームデータにて\n不正行為が疑われる操作を確認したため、\nアカウント連携機能を凍結しております。\n\n<color=red>上記に関して異議申し立ては、\nアカウント連携機能の凍結から1ヶ月以内に\n運営へお問い合わせください。</color>",
                        "<color=#222222>※アカウント連携機能の凍結実施日は\nメールBOXに届いているメールを\nご確認ください。</color>");
                }
            });
        }

        TitleBnIdLinkageResultDialogViewModel CreateResultViewModel(LinkBnIdConfirmModel model)
        {
            var viewModel = TitleBnIdLinkageResultDialogViewModel.Empty;

            if (model.IsLinkable && model.IsAlreadyLinked)
            {
                var newViewModel = viewModel with
                {
                    Title = new TitleBnIdLinkageResultTitle("連携確認"),
                    Message = new TitleBnIdLinkageResultMessage("入力されたバンダイナムコIDには、現在プレイ中のものとは異なるゲームデータが連携されています。\n\n以下のゲームデータに変更しますか？"),
                    MyId = model.UserMyId,
                    Name = model.UserName,
                    Level = model.UserLevel,
                    AttentionMessage = new TitleBnIdLinkageResultAttentionMessage("※現在プレイ中のゲームデータは削除されます。\n※現在プレイ中のゲームデータでアカウント連携を行っていない場合は、ゲームデータが復旧できなくなります。"),
                    LeftButtonTitle = new TitleBnIdLinkageResultLeftButtonTitle("キャンセル"),
                    RightButtonTitle = new TitleBnIdLinkageResultRightButtonTitle("データ変更")
                };

                return newViewModel;
            }
            else if (model.IsLinkable && !model.IsAlreadyLinked)
            {
                var newViewModel = viewModel with
                {
                    Title = new TitleBnIdLinkageResultTitle("アカウント連携"),
                    Message = new TitleBnIdLinkageResultMessage("入力したバンダイナムコIDはゲームデータとのアカウント連携が完了していません。\n\nアカウント連携をすると、複数の端末で同じゲームデータを共有できます。\n\nプレイ中のゲームデータをバンダイナムコIDと連携しますか？"),
                    DateTitle = new TitleBnIdLinkageResultDateTitle("プレイ中のゲームデータ"),
                    MyId = model.UserMyId,
                    Name = model.UserName,
                    Level = model.UserLevel,
                    AttentionMessage = new TitleBnIdLinkageResultAttentionMessage("※一度アカウント連携をすると、\nこのゲームデータに連携する\nバンダイナムコIDは変更できません。\n※有償プリズムは、異なるOS間では共有され\nません。購入したOSでのみ使用できます。"),
                    LeftButtonTitle = new TitleBnIdLinkageResultLeftButtonTitle("キャンセル"),
                    RightButtonTitle = new TitleBnIdLinkageResultRightButtonTitle("アカウント連携")
                };

                return newViewModel;
            }
            else
            {
                switch (model.BnIdLinkRejectionReasonType)
                {
                    case BnIdLinkRejectionReasonType.UserDataNotCreated:
                    {
                        var newViewModel = viewModel with
                        {
                            Title = new TitleBnIdLinkageResultTitle("連携失敗"),
                            Message = new TitleBnIdLinkageResultMessage("この端末はゲームデータ未作成のため、アカウント連携できません。\n\n先にゲームを開始してください。"),
                            RightButtonTitle = new TitleBnIdLinkageResultRightButtonTitle("OK")
                        };
                        return newViewModel;
                    }
                    case BnIdLinkRejectionReasonType.BnIdSwitchingIsNotAllowed:
                    {
                        var newViewModel = viewModel with
                        {
                            Title = new TitleBnIdLinkageResultTitle("連携失敗"),
                            Message = new TitleBnIdLinkageResultMessage("入力したバンダイナムコIDはゲームデータとのアカウント連携が完了していません。\n\nプレイするゲームデータを変更できませんでした。"),
                            LeftButtonTitle = new TitleBnIdLinkageResultLeftButtonTitle("閉じる")
                        };
                        return newViewModel;
                    }
                    case BnIdLinkRejectionReasonType.AlreadyLinkedWithSameBnId:
                    {
                        var newViewModel = viewModel with
                        {
                            Title = new TitleBnIdLinkageResultTitle("確認"),
                            Message = new TitleBnIdLinkageResultMessage("現在プレイ中のゲームデータと\n同じアカウントです。"),
                            RightButtonTitle = new TitleBnIdLinkageResultRightButtonTitle("OK")
                        };
                        return newViewModel;
                    }
                }
            }

            return viewModel;
        }

        (Action, Action) CreateResultButtonActions(LinkBnIdConfirmModel model, TitleBnIdLinkageResultDialogViewController viewController)
        {
            Action leftAction = null;
            Action rightAction = null;

            var viewModel = TitleBnIdLinkageResultDialogViewModel.Empty;
            if (model.IsLinkable && model.IsAlreadyLinked)
            {
                viewModel = viewModel with
                {
                    Title = new TitleBnIdLinkageResultTitle("連携完了"),
                    Message = new TitleBnIdLinkageResultMessage("以下のゲームデータでプレイを開始します。"),
                    MyId = model.UserMyId,
                    Name = model.UserName,
                    Level = model.UserLevel,
                    RightButtonTitle = new TitleBnIdLinkageResultRightButtonTitle("OK")
                };

                rightAction = () =>
                {
                    LinkBnId(model, viewModel, viewController);
                };
            }
            else if (model.IsLinkable && !model.IsAlreadyLinked)
            {
                viewModel = viewModel with
                {
                    Title = new TitleBnIdLinkageResultTitle("連携完了"),
                    Message = new TitleBnIdLinkageResultMessage("アカウント連携が完了しました。\n\n他の端末でアプリを開き、「タイトル画面メニュー」から「アカウント連携」をすると、ゲームデータを共有した状態でプレイを開始できます。"),
                    AttentionMessage = new TitleBnIdLinkageResultAttentionMessage("※一度アカウント連携をすると、\nこのゲームデータに連携する\nバンダイナムコIDは変更できません。\n※有償プリズムは、異なるOS間では共有され\nません。購入したOSでのみ使用できます。"),
                    RightButtonTitle = new TitleBnIdLinkageResultRightButtonTitle("OK")
                };

                rightAction = () =>
                {
                    LinkBnId(model, viewModel, viewController);
                };
            }

            return (leftAction, rightAction);
        }

        void LinkBnId(LinkBnIdConfirmModel model, TitleBnIdLinkageResultDialogViewModel viewModel, TitleBnIdLinkageResultDialogViewController viewController)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    await LinkBnIdForTitleUseCase.LinkBnId(cancellationToken, model.BnIdCode);

                    var argument = new TitleBnIdLinkageResultDialogViewController.Argument(viewModel);
                    var controller = ViewFactory.Create<TitleBnIdLinkageResultDialogViewController, TitleBnIdLinkageResultDialogViewController.Argument>(argument);
                    controller.OnRightButton = () =>
                    {
                        ApplicationRebootor.Reboot();
                        controller.Dismiss();
                    };

                    viewController.PresentModally(controller);
                    viewController.Dismiss();
                }
                catch (UserBnidAccessTokenApiErrorException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "連携失敗",
                        "連携に失敗しました。\n\n再度お試しいただくか、お手数ですがお問い合わせください。");
                }
                catch (UserBnidLinkLimitException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "アカウント連携凍結中",
                        "入力されたバンダイナムコIDに\n連携されたゲームデータは、\n不正が疑われる操作を確認したため\nアカウント連携機能を凍結中です。\n\nゲームデータを共有できませんでした。");
                }
                catch (UserBnidLinkLimitMyAccountException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "アカウント連携停止中",
                        "ご利用中のゲームデータにて\n不正行為が疑われる操作を確認したため、\nアカウント連携機能を凍結しております。\n\n<color=red>上記に関して異議申し立ては、\nアカウント連携機能の凍結から1ヶ月以内に\n運営へお問い合わせください。</color>",
                        "<color=#222222>※アカウント連携機能の凍結実施日は\nメールBOXに届いているメールを\nご確認ください。</color>");
                }
            });
        }

        void ITitleLinkBnIdDialogViewDelegate.OnClose()
        {
            ViewController.Dismiss();
        }
    }
}
