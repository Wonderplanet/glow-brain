using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class MangaAnimationModelFactory : IMangaAnimationModelFactory
    {
        public MangaAnimationModel Create(MstMangaAnimationModel mstModel)
        {
            if (!NeedsModelCreation(mstModel)) return MangaAnimationModel.Empty;

            var remainingAnimationStartDelay = GetRemainingAnimationStartDelay(mstModel);

            return new MangaAnimationModel(
                ActivatedMangaAnimationFlag.False, 
                mstModel.ConditionType,
                mstModel.ConditionValue,
                mstModel.AnimationStartDelay,
                remainingAnimationStartDelay,
                mstModel.AnimationSpeed,
                mstModel.IsPause,
                mstModel.CanSkip,
                mstModel.AssetKey);
        }

        bool NeedsModelCreation(MstMangaAnimationModel mstModel)
        {
            return mstModel.ConditionType != MangaAnimationConditionType.None &&
                   mstModel.ConditionType != MangaAnimationConditionType.Start &&
                   mstModel.ConditionType != MangaAnimationConditionType.Victory &&
                   mstModel.ConditionType != MangaAnimationConditionType.Finish;
        }

        TickCount GetRemainingAnimationStartDelay(MstMangaAnimationModel mstModel)
        {
            if (mstModel.ConditionType == MangaAnimationConditionType.TransformationReady ||
                mstModel.ConditionType == MangaAnimationConditionType.TransformationStart ||
                mstModel.ConditionType == MangaAnimationConditionType.TransformationEnd)
            {
                // 変身時の原画演出はすべて変身演出中（ステージ進行は停止）に出すので、Domain的には演出開始までの残り時間は0
                return TickCount.Zero;
            }

            return mstModel.AnimationStartDelay;
        }
    }
}
