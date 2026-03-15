using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Navigation
{
    public interface IInGameRetrySceneNavigator
    {
        UniTask RetryStage(
            CancellationToken cancellationToken,
            StaminaBoostCount staminaBoostCount,
            AdChallengeFlag isAdChallenge);
    }
}