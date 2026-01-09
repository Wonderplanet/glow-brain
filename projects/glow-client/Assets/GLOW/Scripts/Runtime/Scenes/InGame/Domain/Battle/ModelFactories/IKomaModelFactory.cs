using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IKomaModelFactory
    {
        KomaModel Create(MstKomaModel mstKomaModel);
    }
}
