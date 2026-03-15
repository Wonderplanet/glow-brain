using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record OutpostInitializerResult(
        OutpostModel PlayerOutpost,
        OutpostModel EnemyOutpost);
}
