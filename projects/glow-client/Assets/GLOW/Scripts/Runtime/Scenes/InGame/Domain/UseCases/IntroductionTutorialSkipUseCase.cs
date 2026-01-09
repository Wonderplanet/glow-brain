using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class IntroductionTutorialSkipUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        
        public void SkipIntroductionTutorial()
        {
            // チュートリアルスキップ処理
            InGameScene.IsBattleOver = BattleOverFlag.True;
        }
    }
}