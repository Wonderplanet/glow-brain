using System.Threading;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public interface IInitialAssetLoader
    {
        bool IsCompleted { get; }
        
        void LoadInBackground(InitialLoadAssetsModel initialLoadAssetsModel, CancellationToken cancellationToken);
        void Unload();
    }
}