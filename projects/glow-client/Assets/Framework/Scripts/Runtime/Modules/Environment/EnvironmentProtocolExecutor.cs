using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Environment
{
    internal static class EnvironmentProtocolExecutor
    {
        class FetchEnvironmentResult
        {
            public EnvironmentListData ListData { get; }
            public int Priority { get; }

            public FetchEnvironmentResult(EnvironmentListData listData, int priority)
            {
                ListData = listData;
                Priority = priority;
            }
        }

        public static async UniTask<EnvironmentListData> ExecuteProtocols(CancellationToken cancellationToken, IReadOnlyCollection<IEnvironmentProtocol> protocols)
        {
            // NOTE: 取得処理をタスクに変換する
            var tasks = new UniTask<FetchEnvironmentResult>[protocols.Count];
            for (var i = 0; i < protocols.Count; i++)
            {
                var protocol = protocols.ElementAt(i);
                tasks[i] = UniTask.Create(async () =>
                {
                    var result = await protocol.FetchEnvironmentList(cancellationToken);
                    return new FetchEnvironmentResult(result, protocol.Priority);
                });
            }

            // NOTE: WhenAllで並列に実行する
            var results = await UniTask.WhenAll(tasks);
            // NOTE: resultsをPriority順にソートして最後の結果を返す
            Array.Sort(results, (a, b) => a.Priority - b.Priority);

            // NOTE: Priority順に結果をマージする
            var environmentList = new List<EnvironmentData>();
            foreach (var result in results)
            {
                foreach (var environment in result.ListData.Environments)
                {
                    if (environmentList.Exists(model => model.Env == environment.Env))
                    {
                        continue;
                    }
                    environmentList.Add(environment);
                }
            }

            return new EnvironmentListData(environmentList.ToArray());
        }
    }
}
