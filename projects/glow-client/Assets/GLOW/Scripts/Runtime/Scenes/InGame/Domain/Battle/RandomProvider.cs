using GLOW.Core.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class RandomProvider : IRandomProvider
    {
        public int Range(int min, int max)
        {
            return Random.Range(min, max);
        }

        public float Range(float min, float max)
        {
            return Random.Range(min, max);
        }

        public int Range(int max)
        {
            return Random.Range(0, max);
        }

        /// <summary>
        /// 指定確率でtrueになる
        /// </summary>
        /// <param name="percentage"></param>
        /// <returns></returns>
        public bool Trial(Percentage percentage)
        {
            return Random.Range(0, 100) < percentage.Value;
        }
    }
}
