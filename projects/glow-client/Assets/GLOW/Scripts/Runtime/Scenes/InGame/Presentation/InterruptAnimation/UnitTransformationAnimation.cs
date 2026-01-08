using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Views;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public class UnitTransformationAnimation : IInGameInterruptAnimation
    {
        readonly InGameViewController _viewController;
        readonly UnitTransformationAnimationInfo _animationInfo;

        public InterruptAnimationPriority Priority => InterruptAnimationPriorityDefinitions.UnitTransformation;
        public bool CanSkip => false;

        public UnitTransformationAnimation(
            InGameViewController viewController,
            UnitTransformationAnimationInfo animationInfo)
        {
            _viewController = viewController;
            _animationInfo = animationInfo;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            // 変身後のキャラの表示を一旦消す
            _viewController.SetUnitVisible(_animationInfo.AfterUnitModel.Id, false);

            var viewPauseHandler = _viewController.PauseWithout(_animationInfo.BeforeUnitId);

            try
            {
                // 変身前の原画演出を再生
                var mangaAnimationModelsForTransformationReady = _animationInfo.MangaAnimationModels
                    .Where(model => model.ConditionType == MangaAnimationConditionType.TransformationReady);

                await PlayMangaAnimationsWithUnitTransformation(
                    mangaAnimationModelsForTransformationReady,
                    cancellationToken);

                // 変身させつつ、必要に応じて途中で原画演出を再生
                var transformationTask = _viewController.TransformUnit(
                    _animationInfo.BeforeUnitId,
                    _animationInfo.AfterUnitModel.Id,
                    cancellationToken);

                var mangaAnimationModelsForTransformationStart = _animationInfo.MangaAnimationModels
                    .Where(model => model.ConditionType == MangaAnimationConditionType.TransformationStart);

                var mangaAnimationTask = PlayMangaAnimationsWithUnitTransformation(
                    mangaAnimationModelsForTransformationStart,
                    cancellationToken);

                await (transformationTask, mangaAnimationTask);

                // 変身後の原画演出を再生
                var mangaAnimationModelsForTransformationEnd = _animationInfo.MangaAnimationModels
                    .Where(model => model.ConditionType == MangaAnimationConditionType.TransformationEnd);

                await PlayMangaAnimationsWithUnitTransformation(mangaAnimationModelsForTransformationEnd, cancellationToken);
            }
            finally
            {
                viewPauseHandler?.Dispose();
            }
        }

        async UniTask PlayMangaAnimationsWithUnitTransformation(
            IEnumerable<MangaAnimationModel> mangaAnimationModels,
            CancellationToken cancellationToken)
        {
            var unfinishedMangaAnimationModels = mangaAnimationModels.ToList();

            var startTime = Time.time;
            var animationTime = 0f;

            while (true)
            {
                if (unfinishedMangaAnimationModels.Count == 0) break;

                var time = Time.time;
                var elapsedTime = time - startTime + animationTime;

                foreach (var mangaAnimationModel in unfinishedMangaAnimationModels)
                {
                    if (mangaAnimationModel.AnimationStartDelay.ToSeconds() > elapsedTime) continue;

                    await PlayMangaAnimationWithUnitTransformation(mangaAnimationModel, cancellationToken);
                }

                unfinishedMangaAnimationModels.RemoveAll(model => model.AnimationStartDelay.ToSeconds() <= elapsedTime);

                animationTime += Time.time - time;

                // ReSharper disable once CompareOfFloatsByEqualityOperator
                if (time == Time.time)
                {
                    await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);
                }
            }
        }

        async UniTask PlayMangaAnimationWithUnitTransformation(
            MangaAnimationModel mangaAnimationModel,
            CancellationToken cancellationToken)
        {
            var pauseHandler = _viewController.PauseBattleField();

            try
            {
                await _viewController.PlayMangaAnimation(
                    mangaAnimationModel.AssetKey,
                    mangaAnimationModel.AnimationSpeed,
                    cancellationToken);
            }
            finally
            {
                pauseHandler.Dispose();
            }
        }
    }
}