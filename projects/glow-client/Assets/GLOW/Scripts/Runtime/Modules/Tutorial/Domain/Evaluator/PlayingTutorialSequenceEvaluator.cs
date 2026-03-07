using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Context;

namespace GLOW.Modules.Tutorial.Domain.Evaluator
{
    public static class PlayingTutorialSequenceEvaluator
    {
        public static PlayingTutorialSequenceFlag IsPlayingTutorial(
            ITutorialPlayingStatus tutorialPlayingStatus,
            IFreePartTutorialPlayingStatus freePartTutorialPlayingStatus,
            IPvpTutorialPlayingStatus pvpTutorialPlayingStatus,
            IEventQuestTutorialPlayingStatus eventQuestTutorialPlayingStatus,
            IArtworkEffectTutorialPlayingStatus artworkEffectTutorialPlayingStatus)
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
            
            // いいジャン祭チュートリアル中の場合
            if (eventQuestTutorialPlayingStatus.IsPlayingTutorialSequence)
            {
                return eventQuestTutorialPlayingStatus.IsPlayingTutorialSequence;
            }

            // 原画編成・強化チュートリアル中の場合
            if(artworkEffectTutorialPlayingStatus.IsPlayingTutorialSequence)
            {
                return artworkEffectTutorialPlayingStatus.IsPlayingTutorialSequence;
            }

            // チュートリアル中ではない場合
            return PlayingTutorialSequenceFlag.False;
        }
    }
}
