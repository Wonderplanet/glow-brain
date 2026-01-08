using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.AssetLoaders
{
    public interface IUnitSpecialAttackPreviewSoundEffectLoader
    {
        UniTask Load(UnitAttackViewInfo attackViewInfo, CancellationToken cancellationToken);
        void Unload(UnitAttackViewInfo attackViewInfo);
    }
}