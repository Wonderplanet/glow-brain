using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Domain.UseCase;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.TutorialTipDialog.Presentation.View
{
    public class TutorialTipDialogViewWireFrame
    {
        [Inject] TutorialTipDialogUseCase TutorialTipDialogUseCase { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public void ShowTutorialTipDialog(
            UIViewController parent,
            TutorialTipDialogTitle title,
            TutorialTipAssetPath assetPath,
            Action dialogClosedAction = null)
        {
            var controller = TutorialTipDialogViewController.WithTitleAndAssetPath(
                title,
                assetPath,
                ShouldShowNextButtonTextFlag.False,
                dialogClosedAction);

            // 1.順番依存
            parent.PresentModally(controller);

            var escapeResponder = new TutorialTipDialogViewEscapeResponder(
                controller,
                SoundEffectId.SSE_000_001);
            // 2.順番依存
            EscapeResponderRegistry.Bind(escapeResponder, controller.View);
        }

        public void ShowTutorialTipDialogWithNextButton(
            UIViewController parent,
            TutorialTipDialogTitle title,
            TutorialTipAssetPath assetPath,
            Action dialogClosedAction = null)
        {
            var controller = TutorialTipDialogViewController.WithTitleAndAssetPath(
                title,
                assetPath,
                ShouldShowNextButtonTextFlag.True,
                dialogClosedAction);

            // 1.順番依存
            parent.PresentModally(controller);

            var escapeResponder = new TutorialTipDialogViewEscapeResponder(
                controller,
                SoundEffectId.SSE_000_001);
            // 2.順番依存
            EscapeResponderRegistry.Bind(escapeResponder, controller.View);
        }

        public void ShowTutorialTipDialog(
            UIViewController parent,
            TutorialFunctionName functionName,
            Action dialogClosedAction = null)
        {
            var model = TutorialTipDialogUseCase.GetTutorialTipDialogModel(functionName.ToMasterDataId())
                .TutorialTipModels.FirstOrDefault(TutorialTipModel.Empty);

            if(model.IsEmpty()) return;

            var controller = TutorialTipDialogViewController.WithTitleAndAssetPath(
                model.Title,
                model.AssetPath,
                ShouldShowNextButtonTextFlag.False,
                dialogClosedAction);

            // 1.順番依存
            parent.PresentModally(controller);

            var escapeResponder = new TutorialTipDialogViewEscapeResponder(
                controller,
                SoundEffectId.SSE_000_001);
            // 2.順番依存
            EscapeResponderRegistry.Bind(escapeResponder, controller.View);
        }

        public void ShowTutorialTipDialogs(
            UIViewController parent,
            TutorialFunctionName functionName,
            Action allDialogClosedAction = null)
        {
            var models = TutorialTipDialogUseCase.GetTutorialTipDialogModel(functionName.ToMasterDataId())
                .TutorialTipModels;

            ShowWithModels(parent, models, allDialogClosedAction);
        }

        public void ShowTutorialTipDialogs(
            UIViewController parent,
            IReadOnlyList<TutorialTipModel> models,
            Action allDialogClosedAction = null)
        {
            ShowWithModels(parent, models, allDialogClosedAction);
        }

        void ShowWithModels(
            UIViewController parent,
            IReadOnlyList<TutorialTipModel> models,
            Action allDialogClosedAction = null,
            int index = 0)
        {
            if(models.Count <= index)
            {
                // 全て表示して閉じたらコールバックを呼ぶ
                allDialogClosedAction?.Invoke();
                return;
            }

            var controller = TutorialTipDialogViewController.WithTitleAndAssetPath(
                models[index].Title,
                models[index].AssetPath,
                models[index].ShouldShowNextButtonTextFlag,
                () =>
                {
                    ShowWithModels(parent, models, allDialogClosedAction, index + 1);
                });

            // 1.順番依存
            parent.PresentModally(controller);

            var escapeResponder = new TutorialTipDialogViewEscapeResponder(
                controller,
                SoundEffectId.SSE_000_001);
            // 2.順番依存
            EscapeResponderRegistry.Bind(escapeResponder, controller.View);
        }
    }
}
