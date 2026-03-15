using UnityEngine.UI;
using WonderPlanet.ResourceManagement;

namespace GLOW.Core.Presentation.Modules
{
    public static class SpriteLoaderUtil
    {
        public static void Clear(Image image)
        {
            var loader = image.gameObject.GetComponent<SpriteLoader>();
            if (loader == null) return;

            loader.Clear();
        }
    }
}