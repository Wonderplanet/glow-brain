using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Extensions
{
    // ReSharper disable once InconsistentNaming
    public static class IEnumerableInGameDomainExtension
    {
        public static Damage Sum(this IEnumerable<Damage> source)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            int sum = 0;
            checked
            {
                foreach (var v in source)
                {
                    sum += v.Value;
                }
            }

            return new Damage(sum);
        }

        public static Heal Sum(this IEnumerable<Heal> source)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            int sum = 0;
            checked
            {
                foreach (var v in source)
                {
                    sum += v.Value;
                }
            }

            return new Heal(sum);
        }

        public static BattlePoint Sum(this IEnumerable<BattlePoint> source)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            decimal sum = 0;
            foreach (var v in source)
            {
                sum += v.Value;
            }

            return new BattlePoint(sum);
        }

        public static Percentage Sum(this IEnumerable<Percentage> source)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            int sum = 0;
            checked
            {
                foreach (var v in source)
                {
                    sum += v.Value;
                }
            }

            return new Percentage(sum);
        }

        public static PercentageM Sum(this IEnumerable<PercentageM> source)
        {
            if (source == null)
            {
                throw new ArgumentNullException();
            }

            decimal sum = 0;
            checked
            {
                foreach (var v in source)
                {
                    sum += v.Value;
                }
            }

            return new PercentageM(sum);
        }
    }
}
