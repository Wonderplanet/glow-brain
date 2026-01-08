using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Context;

namespace GLOW.Modules.Tutorial.Domain.Evaluator
{
    public static class PlayingTutorialSequenceEvaluator
    {
        public static PlayingTutorialSequenceFlag IsPlayingTutorial(
            ITutorialPlayingStatus tutorialPlayingStatus, 
            IFreePartTutorialPlayingStatus freePartTutorialPlayingStatus,
            IPvpTutorialPlayingStatus pvpTutorialPlayingStatus)
        {
            // メインパート中の場合
            if (tutorialPlayingStatus.IsPlayingTutorialSequence)
            {
                return tutorialPlayingStatus.IsPlayingTutorialSequence;
            }

            // フリーパート中の場合
            if (freePartTutorialPlayingStatus.IsPlayingTutorialSequence)
            {
                return freePartTutorialPlayingStatus.IsPlayingTutorialSequence;
            }
            
            // PVPチュートリアル中の場合
            if (pvpTutorialPlayingStatus.IsPlayingTutorialSequence)
            {
                return pvpTutorialPlayingStatus.IsPlayingTutorialSequence;
            }

            // チュートリアル中ではない場合
            return PlayingTutorialSequenceFlag.False;
        }
    }
}